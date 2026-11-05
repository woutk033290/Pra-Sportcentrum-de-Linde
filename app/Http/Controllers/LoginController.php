<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function toon(): View|RedirectResponse
    {
        if (Auth::guard('beheer')->check()) {
            return redirect()->route('beheer.dashboard');
        }

        if (Auth::guard('web')->user()?->rol === 'lid') {
            return redirect()->route('rooster');
        }

        return view('loginpagina');
    }

    public function verwerken(Request $request): RedirectResponse
    {
        $gegevens = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'password.required' => 'Vul je wachtwoord in.',
            'password.string' => 'Vul een geldig wachtwoord in.',
        ]);

        // Alleen actieve, niet-verwijderde gebruikers met de rol beheerder mogen erin.
        $gelukt = Auth::guard('beheer')->attempt([
            'email' => $gegevens['email'],
            'password' => $gegevens['password'],
            'rol' => 'beheerder',
            'verwijderd_op' => null,
            fn ($query) => $query->whereNotNull('geactiveerd_op'),
        ]);

        if (! $gelukt) {
            $lidIngelogd = Auth::guard('web')->attempt([
                'email' => $gegevens['email'],
                'password' => $gegevens['password'],
                'rol' => 'lid',
                'verwijderd_op' => null,
                fn ($query) => $query->whereNotNull('geactiveerd_op'),
            ]);

            if ($lidIngelogd) {
                $request->session()->regenerate();

                return redirect()->route('rooster');
            }

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'E-mailadres of wachtwoord is onjuist.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('beheer.dashboard'));
    }

    public function uitloggen(Request $request): RedirectResponse
    {
        Auth::guard('beheer')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
