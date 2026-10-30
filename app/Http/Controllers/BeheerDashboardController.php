<?php

namespace App\Http\Controllers;

use App\Models\Accounttoken;
use App\Models\Gebruiker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        $data = $request->validate([
            'voornaam'       => ['required', 'string', 'max:255'],
            'achternaam'     => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:gebruikers,email'],
            'telefoonnummer' => ['required', 'regex:/^[0-9+\s()\-]{6,20}$/'],
            'rol'            => ['required', 'in:trainer,beheerder'],
            'trainer_bio'    => ['nullable', 'string', 'max:1000'],
        ], [
            'required'             => 'Het veld :attribute is verplicht.',
            'email'                => 'Vul een geldig e-mailadres in.',
            'unique'               => 'Er bestaat al een account met dit e-mailadres.',
            'in'                   => 'Kies een geldige rol.',
            'max.string'           => 'De :attribute is te lang (maximaal :max tekens).',
            'telefoonnummer.regex' => 'Vul een geldig telefoonnummer in.',
        ], [
            'voornaam'       => 'voornaam',
            'achternaam'     => 'achternaam',
            'email'          => 'e-mailadres',
            'telefoonnummer' => 'telefoonnummer',
            'rol'            => 'rol',
            'trainer_bio'    => 'korte intro',
        ]);

        [$collega, $link] = DB::transaction(function () use ($data) {
            // Nog geen wachtwoord en niet geactiveerd: dat doet de collega zelf via de link
            $collega = Gebruiker::create([
                'voornaam'       => $data['voornaam'],
                'achternaam'     => $data['achternaam'],
                'email'          => $data['email'],
                'telefoonnummer' => $data['telefoonnummer'],
                'rol'            => $data['rol'],
                'trainer_bio'    => $data['rol'] === 'trainer' ? ($data['trainer_bio'] ?? null) : null,
            ]);

            return [$collega, $this->maakUitnodiging($collega)];
        });

        return redirect()->route('beheer.dashboard')
            ->with('succes', "{$collega->voornaam} {$collega->achternaam} is toegevoegd als {$collega->rol}. "
                . 'Stuur de collega de activatielink hieronder (7 dagen geldig).')
            ->with('uitnodigingslink', $link);
    }

    public function nieuweUitnodiging(Gebruiker $gebruiker)
    {
        // Alleen voor collega's die hun account nog niet hebben geactiveerd
        abort_unless(
            in_array($gebruiker->rol, ['trainer', 'beheerder'], true)
            && $gebruiker->geactiveerd_op === null
            && $gebruiker->verwijderd_op === null,
            404
        );

        $link = $this->maakUitnodiging($gebruiker);

        return redirect()->route('beheer.dashboard')
            ->with('succes', "Er is een nieuwe activatielink gemaakt voor {$gebruiker->voornaam} {$gebruiker->achternaam} (7 dagen geldig).")
            ->with('uitnodigingslink', $link);
    }

    /** Maakt een activatietoken, mailt de link en geeft de link terug. */
    private function maakUitnodiging(Gebruiker $gebruiker): string
    {
        // Oude, ongebruikte activatielinks van deze collega ongeldig maken
        Accounttoken::where('gebruiker_id', $gebruiker->id)
            ->where('doel', 'activatie')
            ->whereNull('gebruikt_op')
            ->delete();

        $token = Str::random(64);

        Accounttoken::create([
            'gebruiker_id' => $gebruiker->id,
            'token_hash'   => hash('sha256', $token),
            'doel'         => 'activatie',
            'vervalt_op'   => now()->addDays(7),
        ]);

        $link = route('account.activeren', $token);

        try {
            $tekst = "Hallo {$gebruiker->voornaam},\n\n"
                . "Er is een account voor je aangemaakt bij Sportschool De Linde (rol: {$gebruiker->rol}).\n"
                . "Stel je wachtwoord in via deze link (7 dagen geldig):\n\n{$link}\n";

            Mail::raw($tekst, function ($bericht) use ($gebruiker) {
                $bericht->to($gebruiker->email)->subject('Activeer je account - Sportschool De Linde');
            });
        } catch (\Throwable $e) {
            // Mail is niet verplicht: de link wordt ook op het dashboard getoond
            report($e);
        }

        return $link;
    }
}