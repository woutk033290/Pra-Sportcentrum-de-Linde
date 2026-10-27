<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function toon()
    {
        if (Auth::guard('beheer')->check()) {
            return redirect()->route('beheer.dashboard');
        }

        return view('loginpagina');
    }

    public function verwerken(Request $request)
    {
        $gegevens = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Vul je e-mailadres in.',
            'email.email'       => 'Vul een geldig e-mailadres in.',
            'password.required' => 'Vul je wachtwoord in.',
        ]);

        // Alleen actieve, niet-verwijderde gebruikers met de rol beheerder mogen erin.
        $gelukt = Auth::guard('beheer')->attempt([
            'email'         => $gegevens['email'],
            'password'      => $gegevens['password'],
            'rol'           => 'beheerder',
            'verwijderd_op' => null,
            fn ($query) => $query->whereNotNull('geactiveerd_op'),
        ]);

        if (! $gelukt) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'E-mailadres of wachtwoord is onjuist.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('beheer.dashboard'));
    }

    public function uitloggen(Request $request)
    {
        Auth::guard('beheer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}