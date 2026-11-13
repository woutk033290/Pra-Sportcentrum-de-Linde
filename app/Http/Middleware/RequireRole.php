<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $gebruiker = $request->user();

        abort_unless(
            $gebruiker
            && in_array($gebruiker->rol, $roles, true)
            && $gebruiker->geactiveerd_op
            && ! $gebruiker->verwijderd_op,
            403,
            'Je hebt geen toegang tot deze pagina.'
        );

        return $next($request);
    }
}
