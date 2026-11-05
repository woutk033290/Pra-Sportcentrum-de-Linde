<?php

namespace App\Http\Controllers;

use App\Models\Groepsles;
use App\Models\Reservering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReserveringController extends Controller
{
    public function index(Request $request): View
    {
        $reserveringen = Reservering::with('groepsles.trainer')->where('gebruiker_id', $request->user()->id)->where('status', 'bevestigd')->latest()->paginate(20);

        return view('reserveringen', compact('reserveringen'));
    }

    public function store(Request $request, Groepsles $les): RedirectResponse
    {
        DB::transaction(function () use ($request, $les): void {
            $les = Groepsles::lockForUpdate()->findOrFail($les->id);
            $bestaand = Reservering::where('groepsles_id', $les->id)->where('gebruiker_id', $request->user()->id)->first();
            $melding = match (true) {
                $les->status !== 'gepland' || $les->starttijd->lte(now()) => 'Deze les is niet meer beschikbaar.',
                $bestaand?->status === 'bevestigd' => 'Je hebt deze les al gereserveerd.',
                $les->reserveringen()->where('status', 'bevestigd')->count() >= $les->maximaal_aantal_deelnemers => 'Deze les is vol.',default => null
            };
            if ($melding) {
                throw ValidationException::withMessages(['reservering' => $melding]);
            }
            Reservering::updateOrCreate(['groepsles_id' => $les->id, 'gebruiker_id' => $request->user()->id], ['status' => 'bevestigd', 'aangemeld_op' => now(), 'geannuleerd_op' => null]);
        });

        return to_route('reserveringen.index')->with('status', 'Je plek is gereserveerd. Veel plezier!');
    }

    public function destroy(Request $request, Reservering $reservering): RedirectResponse
    {
        abort_unless($reservering->gebruiker_id === $request->user()->id, 403);
        DB::transaction(function () use ($reservering): void {
            $les = Groepsles::lockForUpdate()->findOrFail($reservering->groepsles_id);
            $reservering = Reservering::lockForUpdate()->findOrFail($reservering->id);
            if ($reservering->status !== 'bevestigd') {
                throw ValidationException::withMessages(['reservering' => 'Deze reservering is al geannuleerd.']);
            }
            if (now()->gt($les->starttijd->copy()->subHours(2))) {
                throw ValidationException::withMessages(['reservering' => 'Annuleren kan tot uiterlijk 2 uur voor aanvang.']);
            }
            $reservering->update(['status' => 'geannuleerd', 'geannuleerd_op' => now()]);
        });

        return to_route('reserveringen.index')->with('status', 'De reservering is geannuleerd. De plek is weer vrij.');
    }
}
