<?php

namespace Database\Factories;

use App\Models\Gebruiker;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Gebruiker> */
class GebruikerFactory extends Factory
{
    protected $model = Gebruiker::class;

    /** @return array<string,mixed> */
    public function definition(): array
    {
        return ['voornaam' => fake()->firstName(), 'achternaam' => fake()->lastName(), 'email' => fake()->unique()->safeEmail(), 'telefoonnummer' => '', 'password' => 'wachtwoord', 'rol' => 'lid', 'geactiveerd_op' => now()];
    }

    public function trainer(): static
    {
        return $this->state(fn () => ['rol' => 'trainer']);
    }

    public function beheerder(): static
    {
        return $this->state(fn () => ['rol' => 'beheerder']);
    }
}
