<?php

namespace App\Http\Controllers;

use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Lestype;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RoosterPdfController extends Controller
{
    private function gebruiker()
    {
        $gebruiker = auth('beheer')->user() ?? auth('web')->user();

        abort_unless(
            $gebruiker
            && in_array($gebruiker->rol, ['beheerder', 'trainer', 'lid'], true)
            && $gebruiker->geactiveerd_op
            && ! $gebruiker->verwijderd_op,
            403,
            'Je hebt geen toegang tot deze pagina.'
        );

        return $gebruiker;
    }

    public function formulier()
    {
        $gebruiker = $this->gebruiker();

        return view('pdf.rooster', [
            'trainers' => Gebruiker::where('rol', 'trainer')
                ->whereNull('verwijderd_op')
                ->orderBy('voornaam')
                ->get(),

            'lestypen' => Lestype::orderBy('naam')->get(),

            'isBeheerder' => $gebruiker->rol === 'beheerder',
            'isTrainer' => $gebruiker->rol === 'trainer',
            'isLid' => $gebruiker->rol === 'lid',
        ]);
    }

    public function downloaden(Request $request)
    {
        $gebruiker = $this->gebruiker();

        $data = $request->validate([
            'datum_van' => ['required', 'date'],
            'datum_tot' => [
                'required',
                'date',
                'after_or_equal:datum_van'
            ],

            'trainers' => ['nullable', 'array'],
            'trainers.*' => ['integer', 'exists:gebruikers,id'],

            'lestypen' => ['nullable', 'array'],
            'lestypen.*' => ['integer', 'exists:lestypen,id'],

            'alleen_mijn_lessen' => ['nullable', 'boolean'],
        ]);

        $lessen = Groepsles::with([
            'lestype',
            'trainer',
            'locatie',
            'reserveringen.gebruiker',
        ])
            ->whereBetween('starttijd', [
                Carbon::parse($data['datum_van'])->startOfDay(),
                Carbon::parse($data['datum_tot'])->endOfDay(),
            ]);

        // Trainer mag alleen eigen lessen downloaden.
        if ($gebruiker->rol === 'trainer') {
            $lessen->where('trainer_id', $gebruiker->id);
        }

        // Alleen beheerder mag trainers selecteren.
        if (
            $gebruiker->rol === 'beheerder'
            && !empty($data['trainers'])
        ) {
            $lessen->whereIn('trainer_id', $data['trainers']);
        }

        // Lestypefilter.
        if (!empty($data['lestypen'])) {
            $lessen->whereIn('lestype_id', $data['lestypen']);
        }

        // Lid kan alleen eigen aangemelde lessen bekijken.
        if (
            $gebruiker->rol === 'lid'
            && !empty($data['alleen_mijn_lessen'])
        ) {
            $lessen->whereHas('reserveringen', function ($query) use ($gebruiker) {
                $query->where('gebruiker_id', $gebruiker->id)
                    ->where('status', 'bevestigd');
            });
        }

        $lessen = $lessen
            ->orderBy('starttijd')
            ->get();

        $pdf = Pdf::loadView('pdf.rooster-download', [
            'lessen' => $lessen,
            'datumVan' => $data['datum_van'],
            'datumTot' => $data['datum_tot'],
        ]);

        return $pdf->download('rooster-de-linde.pdf');
    }
}
