<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControleerBeheerder
{
    public function handle(Request $request, Closure $next)
    {
        $gebruiker = Auth::guard('beheer')->user();

        if (! $gebruiker || $gebruiker->rol !== 'beheerder' || $gebruiker->verwijderd_op !== null) {
            Auth::guard('beheer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Je hebt geen toegang meer tot het beheergedeelte.');
        }

        return $next($request);
    }
}