<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless($request->user()?->rol === $role && $request->user()->geactiveerd_op && ! $request->user()->verwijderd_op, 403, 'Je hebt geen toegang tot deze pagina.');

        return $next($request);
    }
}
