<?php

namespace App\Http\Controllers;

use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Lestype;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoosterController extends Controller
{
    public function index(Request $request)
    {
        // Gekozen week (altijd de maandag van die week)
        $start = Carbon::today();

        if ($request->filled('week')) {
            try {
                $start = Carbon::parse($request->query('week'));
            } catch (\Throwable $e) {
                // ongeldige datum: gewoon de huidige week tonen
            }
        }

        $start = $start->startOfWeek();
        $eind  = $start->copy()->addDays(6);

        // Filters
        $gekozenType    = $request->integer('type') ?: null;
        $gekozenTrainer = $request->integer('trainer') ?: null;
        $gekozenDag     = $request->integer('dag') ?: null;

        if ($gekozenDag !== null && ($gekozenDag < 1 || $gekozenDag > 7)) {
            $gekozenDag = null;
        }

        $grid = Groepsles::weekGrid($start, $gekozenType, $gekozenTrainer, $gekozenDag);

        $lestypen = Lestype::orderBy('naam')->get(['id', 'naam']);

        $trainers = Gebruiker::where('rol', 'trainer')
            ->whereNull('verwijderd_op')
            ->orderBy('voornaam')
            ->get(['id', 'voornaam', 'achternaam']);

        // Welke dagkolommen tonen (bij een dagfilter alleen die ene dag)
        $dagen = $gekozenDag ? [$gekozenDag - 1] : [0, 1, 2, 3, 4, 5, 6];

        $maanden = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];

        $weekLabel = 'Week ' . $start->isoWeek
            . ' · ' . $start->day . ' ' . $maanden[$start->month - 1]
            . ' – ' . $eind->day . ' ' . $maanden[$eind->month - 1];

        return view('rooster', [
            'start'          => $start,
            'vandaag'        => Carbon::today(),
            'weekLabel'      => $weekLabel,
            'vorige'         => $start->copy()->subWeek()->toDateString(),
            'volgende'       => $start->copy()->addWeek()->toDateString(),
            'grid'           => $grid,
            'lestypen'       => $lestypen,
            'trainers'       => $trainers,
            'dagen'          => $dagen,
            'gekozenType'    => $gekozenType,
            'gekozenTrainer' => $gekozenTrainer,
            'gekozenDag'     => $gekozenDag,
        ]);
    }
}