<?php

namespace Database\Factories;

use App\Models\Groepsles;
use App\Models\Lestype;
use App\Models\Locatie;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Groepsles> */
class GroepslesFactory extends Factory
{
    protected $model = Groepsles::class;

    /** @return array<string,mixed> */
    public function definition(): array
    {
        return [
            'naam' => 'Testles '.fake()->uuid(), 'beschrijving' => 'Samen bewegen',
            'trainer_id' => GebruikerFactory::new()->trainer(),
            'lestype_id' => fn () => Lestype::create(['naam' => fake()->unique()->word(), 'beschrijving' => 'Training', 'doelgroep' => 'Iedereen', 'niveau' => 'Alle niveaus', 'benodigdheden' => 'Water', 'duur_minuten' => 60, 'standaard_capaciteit' => 20])->id,
            'locatie_id' => fn () => Locatie::create(['naam' => fake()->unique()->word()])->id,
            'starttijd' => now()->addDay(), 'eindtijd' => now()->addDay()->addHour(), 'maximaal_aantal_deelnemers' => 20, 'status' => 'gepland'];
    }
}
