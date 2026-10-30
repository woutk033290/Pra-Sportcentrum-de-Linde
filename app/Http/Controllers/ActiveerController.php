<?php

namespace App\Http\Controllers;

use App\Models\Accounttoken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ActiveerController extends Controller
{
    public function toon(string $token)
    {
        $accounttoken = $this->zoekToken($token);

        return view('activeren', [
            'token'     => $token,
            'geldig'    => $accounttoken !== null,
            'gebruiker' => $accounttoken?->gebruiker,
        ]);
    }

    public function opslaan(Request $request, string $token)
    {
        $accounttoken = $this->zoekToken($token);

        if (! $accounttoken) {
            return redirect()->route('account.activeren', $token);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'Kies een wachtwoord.',
            'password.min'       => 'Je wachtwoord moet minimaal 8 tekens lang zijn.',
            'password.confirmed' => 'De twee wachtwoorden komen niet overeen.',
        ]);

        DB::transaction(function () use ($accounttoken, $data) {
            $accounttoken->gebruiker->update([
                'password'       => Hash::make($data['password']),
                'geactiveerd_op' => now(),
            ]);

            $accounttoken->update(['gebruikt_op' => now()]);
        });

        return redirect()->route('login')
            ->with('succes', 'Je account is geactiveerd. Je kunt nu inloggen.');
    }

    /** Zoekt een geldige (niet gebruikte, niet verlopen) activatietoken. */
    private function zoekToken(string $token): ?Accounttoken
    {
        $accounttoken = Accounttoken::with('gebruiker')
            ->where('token_hash', hash('sha256', $token))
            ->where('doel', 'activatie')
            ->whereNull('gebruikt_op')
            ->where('vervalt_op', '>', now())
            ->first();

        if (
            ! $accounttoken
            || ! $accounttoken->gebruiker
            || $accounttoken->gebruiker->geactiveerd_op !== null
            || $accounttoken->gebruiker->verwijderd_op !== null
        ) {
            return null;
        }

        return $accounttoken;
    }
}