<?php

namespace App\Http\Controllers;

use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Reservering;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LesController extends Controller
{
    public function tonen(Groepsles $groepsles)
    {
        $groepsles->load(['lestype', 'trainer', 'locatie']);

        $gebruiker  = $this->ingelogdeGebruiker();
        $kanBeheren = $this->magBeheren($gebruiker, $groepsles);

        $bevestigd = $groepsles->reserveringen()->where('status', 'bevestigd')->count();
        $vrij      = max(0, $groepsles->maximaal_aantal_deelnemers - $bevestigd);

        // Andere momenten van hetzelfde lestype
        $andere = Groepsles::where('lestype_id', $groepsles->lestype_id)
            ->where('id', '!=', $groepsles->id)
            ->where('status', 'gepland')
            ->where('starttijd', '>', now())
            ->orderBy('starttijd')
            ->limit(4)
            ->get();

        // Deelnemerslijst: alleen voor beheerder / trainer van deze les
        $deelnemers = $kanBeheren
            ? $groepsles->reserveringen()
                ->with('gebruiker')
                ->where('status', 'bevestigd')
                ->orderBy('aangemeld_op')
                ->get()
            : collect();

        $eigenAanmelding = $gebruiker
            ? $groepsles->reserveringen()
                ->where('gebruiker_id', $gebruiker->id)
                ->where('status', 'bevestigd')
                ->exists()
            : false;

        return view('les', [
            'groepsles'       => $groepsles,
            'gebruiker'       => $gebruiker,
            'kanBeheren'      => $kanBeheren,
            'isBeheerder'     => $gebruiker?->rol === 'beheerder',
            'bevestigd'       => $bevestigd,
            'vrij'            => $vrij,
            'andere'          => $andere,
            'deelnemers'      => $deelnemers,
            'eigenAanmelding' => $eigenAanmelding,
        ]);
    }

    /** Een lid meldt zichzelf aan voor een les. */
    public function aanmelden(Groepsles $groepsles)
    {
        $gebruiker = $this->ingelogdeGebruiker();

        if (! $gebruiker) {
            return redirect()->route('login');
        }

        if ($gebruiker->rol !== 'lid') {
            return $this->terug($groepsles, 'fout', 'Alleen leden kunnen zich aanmelden voor een les.');
        }

        $fout = DB::transaction(function () use ($groepsles, $gebruiker) {
            // Vergrendelen zodat twee leden niet tegelijk de laatste plek pakken
            $les = Groepsles::whereKey($groepsles->id)->lockForUpdate()->first();

            if ($les->status !== 'gepland' || $les->starttijd->isPast()) {
                return 'Je kunt je niet meer aanmelden voor deze les.';
            }

            $bevestigd = $les->reserveringen()->where('status', 'bevestigd')->count();

            if ($bevestigd >= $les->maximaal_aantal_deelnemers) {
                return 'Deze les is vol.';
            }

            $bestaand = $les->reserveringen()->where('gebruiker_id', $gebruiker->id)->first();

            if ($bestaand && $bestaand->status === 'bevestigd') {
                return 'Je bent al aangemeld voor deze les.';
            }

            if ($bestaand) {
                // Eerder afgemeld: dezelfde reservering weer activeren (unieke combinatie lid + les)
                $bestaand->update([
                    'status'         => 'bevestigd',
                    'aangemeld_op'   => now(),
                    'geannuleerd_op' => null,
                ]);
            } else {
                Reservering::create([
                    'gebruiker_id' => $gebruiker->id,
                    'groepsles_id' => $les->id,
                    'status'       => 'bevestigd',
                    'aangemeld_op' => now(),
                ]);
            }

            return null;
        });

        if ($fout) {
            return $this->terug($groepsles, 'fout', $fout);
        }

        return $this->terug($groepsles, 'succes', 'Je bent aangemeld voor deze les.');
    }

    /** Beheerder of trainer meldt een deelnemer af, zodat er een plek vrijkomt. */
    public function afmelden(Groepsles $groepsles, Reservering $reservering)
    {
        $this->controleerBeheerrechten($groepsles);

        abort_unless($reservering->groepsles_id === $groepsles->id, 404);

        if ($reservering->status === 'bevestigd') {
            $reservering->update([
                'status'         => 'geannuleerd',
                'geannuleerd_op' => now(),
            ]);
        }

        $naam = $reservering->gebruiker->voornaam . ' ' . $reservering->gebruiker->achternaam;

        return $this->terug($groepsles, 'succes', "{$naam} is afgemeld. Er is weer een plek vrij.");
    }

    /** Beheerder of trainer annuleert de hele les. */
    public function annuleren(Groepsles $groepsles)
    {
        $this->controleerBeheerrechten($groepsles);

        $groepsles->update(['status' => 'geannuleerd']);

        return $this->terug($groepsles, 'succes', "De les {$groepsles->naam} is geannuleerd.");
    }

    /* ---------------------------------------------------------------
     | Hulpmethodes
     * ------------------------------------------------------------- */

    /** Zoekt de ingelogde Gebruiker in de beheer-guard en daarna in de standaard guard. */
    private function ingelogdeGebruiker(): ?Gebruiker
    {
        foreach (['beheer', 'web'] as $guard) {
            $gebruiker = Auth::guard($guard)->user();

            if ($gebruiker instanceof Gebruiker) {
                return $gebruiker;
            }
        }

        return null;
    }

    /** Beheerders mogen alles; een trainer alleen zijn eigen lessen. */
    private function magBeheren(?Gebruiker $gebruiker, Groepsles $groepsles): bool
    {
        if (! $gebruiker) {
            return false;
        }

        if ($gebruiker->rol === 'beheerder') {
            return true;
        }

        return $gebruiker->rol === 'trainer' && $gebruiker->id === $groepsles->trainer_id;
    }

    private function controleerBeheerrechten(Groepsles $groepsles): void
    {
        abort_unless($this->magBeheren($this->ingelogdeGebruiker(), $groepsles), 403);
    }

    private function terug(Groepsles $groepsles, string $soort, string $melding)
    {
        return redirect()->route('les.tonen', $groepsles)->with($soort, $melding);
    }
}