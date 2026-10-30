<?php

namespace App\Http\Controllers;

use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Lesreeks;
use App\Models\Lestype;
use App\Models\Locatie;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BeheerLesController extends Controller
{
    public function index(Request $request)
    {
        $zoek = $request->string('zoek')->trim()->toString();

        $lessen = Groepsles::with('lestype')
            ->where('starttijd', '>=', Carbon::today())
            ->when($zoek !== '', function ($query) use ($zoek) {
                $query->where(function ($query) use ($zoek) {
                    $query->where('naam', 'like', "%{$zoek}%")
                        ->orWhereHas('lestype', fn ($type) => $type->where('naam', 'like', "%{$zoek}%"));
                });
            })
            ->orderBy('starttijd')
            ->paginate(8)
            ->withQueryString();

        return view('beheer.lessen', [
            'lessen'   => $lessen,
            'zoek'     => $zoek,
            'lestypen' => Lestype::orderBy('naam')->get(),
            'trainers' => $this->trainers(),
            'locaties' => Locatie::orderBy('naam')->get(),
        ]);
    }

    public function opslaan(Request $request)
    {
        $data = $request->validate([
            'naam'                       => ['required', 'string', 'max:255'],
            'lestype_id'                 => ['required', 'integer', 'exists:lestypen,id'],
            'datum'                      => ['required', 'date', 'after_or_equal:today'],
            'starttijd'                  => ['required', 'date_format:H:i'],
            'eindtijd'                   => ['required', 'date_format:H:i', 'after:starttijd'],
            // De beheerder mag elke (niet verwijderde) trainer kiezen
            'trainer_id'                 => [
                'required',
                'integer',
                Rule::exists('gebruikers', 'id')->where(
                    fn ($q) => $q->where('rol', 'trainer')->whereNull('verwijderd_op')
                ),
            ],
            'locatie_id'                 => ['required', 'integer', 'exists:locaties,id'],
            'maximaal_aantal_deelnemers' => ['required', 'integer', 'min:1', 'max:200'],
            'beschrijving'               => ['required', 'string', 'max:2000'],
            'foto'                       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'herhalen'                   => ['required', 'in:0,1,2'],
            'herhalen_tot'               => ['nullable', 'required_unless:herhalen,0', 'date', 'after_or_equal:datum'],
        ], [
            'required'                   => 'Het veld :attribute is verplicht.',
            'required_unless'            => 'Het veld :attribute is verplicht bij een herhaling.',
            'exists'                     => 'Kies een geldige :attribute.',
            'date'                       => 'Vul een geldige :attribute in.',
            'date_format'                => 'Vul een geldige :attribute in.',
            'integer'                    => 'De :attribute moet een getal zijn.',
            'in'                         => 'Kies een geldige :attribute.',
            'image'                      => 'De foto moet een afbeelding zijn.',
            'mimes'                      => 'De foto moet een jpg, png of webp zijn.',
            'max.string'                 => 'De :attribute is te lang (maximaal :max tekens).',
            'max.numeric'                => 'De :attribute mag maximaal :max zijn.',
            'max.file'                   => 'De foto mag maximaal 2 MB zijn.',
            'min.numeric'                => 'De :attribute moet minimaal :min zijn.',
            'datum.after_or_equal'       => 'De datum mag niet in het verleden liggen.',
            'eindtijd.after'             => 'De eindtijd moet na de starttijd liggen.',
            'herhalen_tot.after_or_equal' => 'De einddatum van de herhaling moet op of na de datum van de les liggen.',
        ], [
            'naam'                       => 'naam',
            'lestype_id'                 => 'type',
            'datum'                      => 'datum',
            'starttijd'                  => 'starttijd',
            'eindtijd'                   => 'eindtijd',
            'trainer_id'                 => 'trainer',
            'locatie_id'                 => 'zaal',
            'maximaal_aantal_deelnemers' => 'maximaal aantal deelnemers',
            'beschrijving'               => 'beschrijving',
            'herhalen'                   => 'herhaling',
            'herhalen_tot'               => 'einddatum van de herhaling',
        ]);

        // Alle datums waarop de les plaatsvindt (1 bij eenmalig)
        $interval = (int) $data['herhalen'];
        $eerste   = Carbon::parse($data['datum']);
        $datums   = [$eerste->copy()];

        if ($interval > 0) {
            $laatste = Carbon::parse($data['herhalen_tot']);

            for ($d = $eerste->copy()->addWeeks($interval); $d->lte($laatste); $d->addWeeks($interval)) {
                $datums[] = $d->copy();
            }
        }

        if (count($datums) > 52) {
            return back()->withInput()->withErrors([
                'herhalen_tot' => 'Een reeks mag maximaal 52 lessen bevatten.',
            ]);
        }

        // Dubbele boekingen voorkomen (trainer of zaal al bezet)
        foreach ($datums as $datum) {
            $start = $datum->copy()->setTimeFromTimeString($data['starttijd']);
            $eind  = $datum->copy()->setTimeFromTimeString($data['eindtijd']);

            $bezet = Groepsles::where('status', 'gepland')
                ->where('starttijd', '<', $eind)
                ->where('eindtijd', '>', $start)
                ->where(function ($q) use ($data) {
                    $q->where('trainer_id', $data['trainer_id'])
                        ->orWhere('locatie_id', $data['locatie_id']);
                })
                ->first();

            if ($bezet) {
                $wie = (int) $bezet->trainer_id === (int) $data['trainer_id'] ? 'De trainer is' : 'De zaal is';

                return back()->withInput()->withErrors([
                    'starttijd' => "{$wie} op {$datum->format('d-m-Y')} al ingepland voor {$bezet->naam} "
                        . "({$bezet->starttijd->format('H:i')} - {$bezet->eindtijd->format('H:i')}).",
                ]);
            }
        }

        $fotoPad = $request->hasFile('foto')
            ? $request->file('foto')->store('lessen', 'public')
            : null;

        DB::transaction(function () use ($data, $datums, $interval, $fotoPad) {
            $reeksId = null;

            if ($interval > 0) {
                $reeksId = Lesreeks::create([
                    'interval_weken' => $interval,
                    'startdatum'     => $datums[0]->toDateString(),
                    'einddatum'      => $data['herhalen_tot'],
                ])->id;
            }

            foreach ($datums as $datum) {
                Groepsles::create([
                    'lestype_id'                 => $data['lestype_id'],
                    'trainer_id'                 => $data['trainer_id'],
                    'locatie_id'                 => $data['locatie_id'],
                    'lesreeks_id'                => $reeksId,
                    'naam'                       => $data['naam'],
                    'beschrijving'               => $data['beschrijving'],
                    'foto_pad'                   => $fotoPad,
                    'starttijd'                  => $datum->copy()->setTimeFromTimeString($data['starttijd']),
                    'eindtijd'                   => $datum->copy()->setTimeFromTimeString($data['eindtijd']),
                    'maximaal_aantal_deelnemers' => $data['maximaal_aantal_deelnemers'],
                    'status'                     => 'gepland',
                ]);
            }
        });

        $aantal = count($datums);
        $melding = $aantal === 1
            ? "De les {$data['naam']} is toegevoegd aan het rooster."
            : "{$aantal} lessen van {$data['naam']} zijn toegevoegd aan het rooster.";

        return redirect()->route('beheer.lessen')->with('succes', $melding);
    }

    private function trainers()
    {
        return Gebruiker::where('rol', 'trainer')
            ->whereNull('verwijderd_op')
            ->orderBy('voornaam')
            ->get(['id', 'voornaam', 'achternaam']);
    }
}