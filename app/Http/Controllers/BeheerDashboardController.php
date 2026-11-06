<?php

namespace App\Http\Controllers;

use App\Models\Gebruiker;
use App\Models\Groepsles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BeheerDashboardController extends Controller
{
    public function index()
    {
        $collegas = Gebruiker::whereIn('rol', ['trainer', 'beheerder'])
            ->whereNull('verwijderd_op')
            ->orderBy('rol')
            ->orderBy('voornaam')
            ->get();

        return view('beheer.dashboard', ['collegas' => $collegas]);
    }

    public function collegaOpslaan(Request $request)
    {
        $email = $request->string('email')->toString();

        // Het adres van een verwijderd account weer vrijmaken, zodat het opnieuw gebruikt kan worden
        Gebruiker::geefEmailVrij($email);

        // Een collega uit de oude uitnodigingsflow (nog geen wachtwoord) mag opnieuw worden ingevuld
        $wachtend = Gebruiker::where('email', $email)
            ->whereNull('geactiveerd_op')
            ->whereNull('password')
            ->whereNull('verwijderd_op')
            ->whereIn('rol', ['trainer', 'beheerder'])
            ->first();

        $data = $request->validate([
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('gebruikers', 'email')->ignore($wachtend?->id)],
            'telefoonnummer' => ['required', 'regex:/^[0-9+\s()\-]{6,20}$/'],
            'rol' => ['required', 'in:trainer,beheerder'],
            'trainer_bio' => ['nullable', 'string', 'max:1000'],
            'profielfoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => 'Het veld :attribute is verplicht.',
            'email' => 'Vul een geldig e-mailadres in.',
            'unique' => 'Er bestaat al een account met dit e-mailadres.',
            'in' => 'Kies een geldige rol.',
            'max.string' => 'De :attribute is te lang (maximaal :max tekens).',
            'telefoonnummer.regex' => 'Vul een geldig telefoonnummer in.',
            'profielfoto.image' => 'De foto moet een afbeelding zijn.',
            'profielfoto.mimes' => 'De foto moet een jpg, png of webp zijn.',
            'profielfoto.max' => 'De foto mag maximaal 2 MB groot zijn.',
            'profielfoto.uploaded' => 'De foto kon niet worden geüpload (maximaal 2 MB).',
            'password.min' => 'Het wachtwoord moet minimaal 8 tekens lang zijn.',
            'password.confirmed' => 'De twee wachtwoorden komen niet overeen.',
        ], [
            'voornaam' => 'voornaam',
            'achternaam' => 'achternaam',
            'email' => 'e-mailadres',
            'telefoonnummer' => 'telefoonnummer',
            'rol' => 'rol',
            'trainer_bio' => 'korte intro',
            'password' => 'wachtwoord',
        ]);

        $gegevens = [
            'voornaam' => $data['voornaam'],
            'achternaam' => $data['achternaam'],
            'email' => $data['email'],
            'telefoonnummer' => $data['telefoonnummer'],
            'rol' => $data['rol'],
            'trainer_bio' => $data['rol'] === 'trainer' ? ($data['trainer_bio'] ?? null) : null,
            'password' => Hash::make($data['password']),
            'geactiveerd_op' => now(), // de beheerder stelt het wachtwoord in, dus meteen actief
        ];

        if ($request->hasFile('profielfoto')) {
            $gegevens['profielfoto_pad'] = $request->file('profielfoto')->store('profielfoto', 'public');
        }

        if ($wachtend) {
            $wachtend->update($gegevens);
            $collega = $wachtend;
        } else {
            $collega = Gebruiker::create($gegevens);
        }

        return redirect()->route('beheer.dashboard')
            ->with('succes', "{$collega->voornaam} {$collega->achternaam} is toegevoegd als {$collega->rol} "
                . 'en kan nu inloggen met het ingestelde wachtwoord.');
    }

    /** Foto van een bestaande collega (trainer of beheerder) toevoegen, vervangen of weghalen. */
    public function collegaFoto(Request $request, Gebruiker $gebruiker)
    {
        abort_unless(
            in_array($gebruiker->rol, ['trainer', 'beheerder'], true) && $gebruiker->verwijderd_op === null,
            404
        );

        $request->validate([
            'profielfoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'profielfoto.image' => 'De foto moet een afbeelding zijn.',
            'profielfoto.mimes' => 'De foto moet een jpg, png of webp zijn.',
            'profielfoto.max' => 'De foto mag maximaal 2 MB groot zijn.',
            'profielfoto.uploaded' => 'De foto kon niet worden geüpload (maximaal 2 MB).',
        ]);

        $oud = $gebruiker->profielfoto_pad;

        if ($request->boolean('verwijder_foto') && ! $request->hasFile('profielfoto')) {
            $gebruiker->update(['profielfoto_pad' => null]);
            $melding = 'De foto van '.$gebruiker->voornaam.' is verwijderd.';
        } elseif ($request->hasFile('profielfoto')) {
            $gebruiker->update([
                'profielfoto_pad' => $request->file('profielfoto')->store('profielfoto', 'public'),
            ]);
            $melding = 'De foto van '.$gebruiker->voornaam.' is opgeslagen.';
        } else {
            return redirect()->route('beheer.dashboard')->with('fout', 'Kies eerst een foto om te uploaden.');
        }

        // Het oude bestand opruimen, maar alleen als het in onze eigen map staat
        if ($oud && str_starts_with($oud, 'profielfoto/')) {
            Storage::disk('public')->delete($oud);
        }

        return redirect()->route('beheer.dashboard')->with('succes', $melding);
    }

    public function collegaVerwijderen(Gebruiker $gebruiker)
    {
        abort_unless(
            in_array($gebruiker->rol, ['trainer', 'beheerder'], true) && $gebruiker->verwijderd_op === null,
            404
        );

        // Je kunt jezelf niet verwijderen
        if ($gebruiker->id === auth('beheer')->id()) {
            return redirect()->route('beheer.dashboard')
                ->with('fout', 'Je kunt je eigen account niet verwijderen.');
        }

        // Een trainer met geplande lessen eerst vervangen, anders staan er lessen zonder trainer in het rooster
        $aantalLessen = Groepsles::where('trainer_id', $gebruiker->id)
            ->where('status', 'gepland')
            ->where('starttijd', '>', now())
            ->count();

        if ($aantalLessen > 0) {
            return redirect()->route('beheer.dashboard')->with(
                'fout',
                "{$gebruiker->voornaam} {$gebruiker->achternaam} heeft nog {$aantalLessen} geplande les(sen). "
                . 'Pas eerst de trainer van die lessen aan (bij Lessen beheren) en verwijder daarna het account.'
            );
        }

        $naam = "{$gebruiker->voornaam} {$gebruiker->achternaam}";

        // E-mailadres vrijmaken (de kolom is uniek), zodat het adres opnieuw gebruikt kan worden
        $gebruiker->update([
            'verwijderd_op' => now(),
            'email' => mb_substr("verwijderd-{$gebruiker->id}-{$gebruiker->email}", 0, 255),
        ]);

        return redirect()->route('beheer.dashboard')
            ->with('succes', "{$naam} is verwijderd en heeft geen toegang meer.");
    }
}