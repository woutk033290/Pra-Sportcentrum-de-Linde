<?php

namespace App\Http\Controllers;

use App\Models\Accounttoken;
use App\Models\Gebruiker;
use App\Notifications\AccountActivatie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'voornaam' => ['required', 'string', 'max:255'],
            'achternaam' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:gebruikers,email'],
            'telefoonnummer' => ['nullable', 'string', 'max:255'],
            'geboortedatum' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'profielfoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'voorwaarden' => ['accepted'],
        ], [
            'profielfoto.image' => 'De profielfoto moet een afbeelding zijn.',
            'profielfoto.mimes' => 'De profielfoto moet een jpg, png of webp zijn.',
            'profielfoto.max' => 'De profielfoto mag maximaal 2 MB groot zijn.',
            'profielfoto.uploaded' => 'De profielfoto kon niet worden geüpload (maximaal 2 MB).',
            'required' => ':attribute is verplicht.',
            'string' => ':attribute moet tekst zijn.',
            'max' => ':attribute mag maximaal :max tekens bevatten.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'email.unique' => 'Dit e-mailadres is al in gebruik.',
            'password.min' => 'Het wachtwoord moet minimaal 8 tekens bevatten.',
            'password.confirmed' => 'De wachtwoorden komen niet overeen.',
            'geboortedatum.date_format' => 'Vul een geldige geboortedatum in.',
            'geboortedatum.before_or_equal' => 'De geboortedatum mag niet in de toekomst liggen.',
            'voorwaarden.accepted' => 'Ga akkoord met de voorwaarden.',
        ], [
            'voornaam' => 'Voornaam', 'achternaam' => 'Achternaam',
            'email' => 'E-mailadres', 'password' => 'Wachtwoord',
            'telefoonnummer' => 'Telefoonnummer',
        ]);

        Gebruiker::create([
            'voornaam' => $data['voornaam'],
            'achternaam' => $data['achternaam'],
            'email' => $data['email'],
            'profielfoto_pad' => $request->hasFile('profielfoto')
                ? $request->file('profielfoto')->store('profielfoto', 'public')
                : null,
            'telefoonnummer' => $data['telefoonnummer'] ?? '',
            'geboortedatum' => $data['geboortedatum'] ?? null,
            'password' => Hash::make($data['password']),
            'rol' => 'lid',
            'voorwaarden_akkoord_op' => now(),
            'geactiveerd_op' => now(),
        ]);

        return to_route('login')->with('succes', 'Je account is aangemaakt. Je kunt nu inloggen.');
    }

    public function collega(Request $request): RedirectResponse
    {
        $data = $request->validate(['naam' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:gebruikers,email'], 'rol' => ['required', Rule::in(['trainer', 'beheerder'])]]);
        $token = Str::random(64);
        $gebruiker = DB::transaction(function () use ($data, $token): Gebruiker {
            $gebruiker = Gebruiker::create(['voornaam' => $data['naam'], 'achternaam' => '', 'telefoonnummer' => '', 'email' => $data['email'], 'rol' => $data['rol']]);
            Accounttoken::create(['gebruiker_id' => $gebruiker->id, 'token_hash' => hash('sha256', $token), 'doel' => 'activatie', 'vervalt_op' => now()->addDays(2)]);

            return $gebruiker;
        });
        $gebruiker->notify(new AccountActivatie($token));

        return back()->with('status', 'De collega is toegevoegd en de activatiemail is verstuurd.');
    }

    private function validToken(string $token): Accounttoken
    {
        $accounttoken = Accounttoken::where('token_hash', hash('sha256', $token))->where('doel', 'activatie')->whereNull('gebruikt_op')->where('vervalt_op', '>', now())->first();
        abort_unless($accounttoken, 410, 'Deze activatielink is verlopen of al gebruikt.');

        return $accounttoken;
    }

    public function activation(string $token): View
    {
        $this->validToken($token);

        return view('auth.activatie', compact('token'));
    }

    public function activate(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        DB::transaction(function () use ($token, $data): void {
            $accounttoken = Accounttoken::whereKey($this->validToken($token)->id)->lockForUpdate()->firstOrFail();
            if ($accounttoken->gebruikt_op || $accounttoken->vervalt_op->lte(now())) {
                throw ValidationException::withMessages(['password' => 'Deze activatielink is verlopen of al gebruikt.']);
            }
            Gebruiker::findOrFail($accounttoken->gebruiker_id)->update(['password' => $data['password'], 'geactiveerd_op' => now()]);
            $accounttoken->update(['gebruikt_op' => now()]);
        });

        return to_route('login')->with('status', 'Je account is geactiveerd. Je kunt nu inloggen.');
    }
}
