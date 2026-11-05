<?php

namespace App\Http\Controllers;

use App\Models\Groepsles;
use App\Models\Lestype;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Altijd de huidige week (maandag t/m zondag)
        $start = Carbon::today()->startOfWeek();

        $gekozenType = $request->integer('type') ?: null;

        return view('index', [
            'lestypen'    => Lestype::orderBy('naam')->get(),
            'start'       => $start,
            'vandaag'     => Carbon::today(),
            'grid'        => Groepsles::weekGrid($start, $gekozenType),
            'dagen'       => [0, 1, 2, 3, 4, 5, 6],
            'gekozenType' => $gekozenType,
        ]);
    }
}