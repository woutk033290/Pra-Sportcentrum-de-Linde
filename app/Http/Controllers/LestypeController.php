<?php

namespace App\Http\Controllers;

use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Lestype;
use App\Models\Reservering;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LestypeController extends Controller
{
    public function tonen(string $slug)
    {
        // /lessen/spinning -> het lestype waarvan de naam "spinning" wordt
        $lestype = Lestype::all()->first(fn (Lestype $type) => Str::slug($type->naam) === $slug);

        abort_if($lestype === null, 404);

        // De eerstvolgende lessen van dit lestype
        $lessen = Groepsles::with(['trainer', 'locatie'])
            ->withCount(['reserveringen as bevestigd_aantal' => fn ($q) => $q->where('status', 'bevestigd')])
            ->where('lestype_id', $lestype->id)
            ->where('status', 'gepland')
            ->where('starttijd', '>', now())
            ->orderBy('starttijd')
            ->limit(5)
            ->get();

        // De trainer van de eerstvolgende les
        $trainer = $lessen->first()?->trainer;

        $gebruiker = $this->ingelogdeGebruiker();

        // Voor welke van deze lessen is de ingelogde gebruiker al aangemeld?
        $aangemeld = ($gebruiker && $lessen->isNotEmpty())
            ? Reservering::where('gebruiker_id', $gebruiker->id)
                ->where('status', 'bevestigd')
                ->whereIn('groepsles_id', $lessen->pluck('id'))
                ->pluck('groepsles_id')
                ->all()
            : [];

        return view('lestype', [
            'lestype'   => $lestype,
            'lessen'    => $lessen,
            'trainer'   => $trainer,
            'gebruiker' => $gebruiker,
            'aangemeld' => $aangemeld,
        ]);
    }

    /** Zoekt de ingelogde Gebruiker in de beheer-guard en daarna in de standaard guard. */
    private function ingelogdeGebruiker(): ?Gebruiker
    {
        foreach (['beheer', 'web'] as $guard) {
            $gebruiker = Auth::guard($guard)->user();

            if ($gebruiker instanceof Gebruiker && $gebruiker->verwijderd_op === null) {
                return $gebruiker;
            }
        }

        return null;
    }
}