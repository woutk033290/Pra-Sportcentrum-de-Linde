<?php

namespace Database\Seeders;

use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Lestype;
use App\Models\Locatie;
use App\Models\Reservering;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $spinning = Lestype::create([
            'naam' => 'Spinning',
            'beschrijving' => 'Intensieve fietsles',
            'doelgroep' => 'Volwassenen',
            'niveau' => 'Gemiddeld',
            'benodigdheden' => 'Handdoek en water',
            'duur_minuten' => 45,
            'standaard_capaciteit' => 20,
        ]);

        $yoga = Lestype::create([
            'naam' => 'Yoga',
            'beschrijving' => 'Rustige stretch- en ademhalingsles',
            'doelgroep' => 'Iedereen',
            'niveau' => 'Beginner',
            'benodigdheden' => 'Yogamat',
            'duur_minuten' => 60,
            'standaard_capaciteit' => 15,
        ]);

        $boot = Lestype::create([
            'naam' => 'Bootcamp',
            'beschrijving' => 'Krachttraining buiten',
            'doelgroep' => 'Volwassenen',
            'niveau' => 'Gevorderd',
            'benodigdheden' => 'Sportkleding en water',
            'duur_minuten' => 60,
            'standaard_capaciteit' => 25,
        ]);

        $aqua = Lestype::create([
            'naam' => 'Aquagym',
            'beschrijving' => 'Bewegen in het water',
            'doelgroep' => 'Iedereen',
            'niveau' => 'Beginner',
            'benodigdheden' => 'Zwemkleding en handdoek',
            'duur_minuten' => 45,
            'standaard_capaciteit' => 12,
        ]);
        $locatie = Locatie::create(['naam' => 'Zaal 1']);

        $trainer = Gebruiker::create([
            'voornaam' => 'Test',
            'achternaam' => 'Trainer',
            'email' => 'trainer@example.com',
            'telefoonnummer' => '0612345678',
            'password' => bcrypt('wachtwoord'),
            'rol' => 'trainer',
            'voorwaarden_akkoord_op' => now(),
            'geactiveerd_op' => now(),
        ]);

        Gebruiker::create([
            'voornaam' => 'Test',
            'achternaam' => 'Beheerder',
            'email' => 'beheerder@example.com',
            'telefoonnummer' => '0687654321',
            'password' => bcrypt('wachtwoord'),
            'rol' => 'beheerder',
            'voorwaarden_akkoord_op' => now(),
            'geactiveerd_op' => now(),
        ]);

        $maandag = now()->startOfWeek();

        foreach ([
            [$spinning, 0, '19:00'],
            [$boot, 2, '07:00'],
            [$spinning, 4, '18:00'],
            [$yoga, 3, '10:00'],
        ] as [$type, $dag, $tijd]) {
            $start = $maandag->copy()->addDays($dag)->setTimeFromTimeString($tijd);

            Groepsles::create([
                'lestype_id' => $type->id,
                'trainer_id' => $trainer->id,
                'locatie_id' => $locatie->id,
                'naam' => $type->naam,
                'beschrijving' => $type->beschrijving,
                'starttijd' => $start,
                'eindtijd' => $start->copy()->addMinutes($type->duur_minuten),
                'maximaal_aantal_deelnemers' => $type->standaard_capaciteit,
                'status' => 'gepland',
            ]);
        }
        $les = Groepsles::orderBy('starttijd')->first();

        foreach ([['Anna', 'de Vries'], ['Jan', 'Bakker'], ['Lisa', 'Smit']] as $i => [$voornaam, $achternaam]) {
            $lid = Gebruiker::create([
                'voornaam' => $voornaam,
                'achternaam' => $achternaam,
                'email' => strtolower($voornaam) . '@example.com',
                'telefoonnummer' => '060000000' . $i,
                'password' => bcrypt('wachtwoord'),
                'rol' => 'lid',
                'voorwaarden_akkoord_op' => now(),
                'geactiveerd_op' => now(),
            ]);

            Reservering::create([
                'gebruiker_id' => $lid->id,
                'groepsles_id' => $les->id,
                'status' => 'bevestigd',
                'aangemeld_op' => now(),
            ]);
        }
    }
}