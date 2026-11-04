<?php

namespace App\Http\Controllers;

use App\Http\Requests\LesRequest;
use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Lestype;
use App\Models\Locatie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BeheerController extends Controller
{
    public function index(): View
    {
        return view('beheer.overzicht', ['lessen' => Groepsles::with('trainer')->withCount('reserveringen')->where('status', 'gepland')->orderBy('starttijd')->paginate(20)]);
    }

    public function create(): View
    {
        return $this->form(new Groepsles);
    }

    public function edit(Groepsles $les): View
    {
        return $this->form($les);
    }

    private function form(Groepsles $les): View
    {
        return view('beheer.les', [
            'les' => $les, 'trainers' => Gebruiker::where('rol', 'trainer')->whereNull('verwijderd_op')->orderBy('voornaam')->get(),
            'lestypen' => Lestype::orderBy('naam')->get(), 'locaties' => Locatie::orderBy('naam')->get()]);
    }

    public function store(LesRequest $request): RedirectResponse
    {
        Groepsles::create([...$request->validated(), 'beschrijving' => $request->input('beschrijving') ?? '', 'status' => 'gepland']);

        return to_route('beheer.dashboard')->with('status', 'De groepsles is toegevoegd.');
    }

    public function update(LesRequest $request, Groepsles $les): RedirectResponse
    {
        DB::transaction(function () use ($request, $les): void {
            $les = Groepsles::lockForUpdate()->findOrFail($les->id);
            if ($request->integer('maximaal_aantal_deelnemers') < $les->reserveringen()->count()) {
                throw ValidationException::withMessages(['maximaal_aantal_deelnemers' => 'Het maximum mag niet lager zijn dan het aantal reserveringen.']);
            }
            $les->update([...$request->validated(), 'beschrijving' => $request->input('beschrijving') ?? '']);
        });

        return to_route('beheer.dashboard')->with('status', 'De groepsles is aangepast.');
    }

    public function destroy(Groepsles $les): RedirectResponse
    {
        DB::transaction(function () use ($les): void {
            $les = Groepsles::lockForUpdate()->findOrFail($les->id);
            $les->reserveringen()->update(['status' => 'geannuleerd', 'geannuleerd_op' => now()]);
            $les->update(['status' => 'geannuleerd']);
        });

        return to_route('beheer.dashboard')->with('status', 'De les is uit het rooster verwijderd.');
    }

    public function deelnemers(Groepsles $les): View
    {
        return view('beheer.deelnemers', ['les' => $les, 'reserveringen' => $les->reserveringen()->with('gebruiker')->get()]);
    }
}
