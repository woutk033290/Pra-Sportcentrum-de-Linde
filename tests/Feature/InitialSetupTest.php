<?php

use App\Models\Gebruiker;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);
test('initial catalog can be seeded repeatedly without creating accounts', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);
    $this->assertDatabaseCount('lestypen', 4);
    $this->assertDatabaseCount('locaties', 1);
    $this->assertDatabaseCount('gebruikers', 0);
});
test('administrator setup hashes the chosen password', function () {
    $this->artisan('sportcentrum:beheerder')
        ->expectsQuestion('Naam', 'Eigen Beheerder')->expectsQuestion('E-mailadres', 'beheer@example.com')
        ->expectsQuestion('Wachtwoord (minimaal 12 tekens)', 'eenveiligwachtwoord')->expectsQuestion('Herhaal wachtwoord', 'eenveiligwachtwoord')
        ->expectsOutput('Beheerder aangemaakt. Je kunt nu inloggen op de website.')->assertExitCode(0);
    $gebruiker = Gebruiker::where('email', 'beheer@example.com')->firstOrFail();
    expect($gebruiker->rol)->toBe('beheerder');
    expect(Hash::check('eenveiligwachtwoord', $gebruiker->password))->toBeTrue();
});
test('administrator setup rejects invalid input without creating an account', function () {
    $this->artisan('sportcentrum:beheerder')
        ->expectsQuestion('Naam', 'Eigen Beheerder')->expectsQuestion('E-mailadres', 'ongeldig')
        ->expectsQuestion('Wachtwoord (minimaal 12 tekens)', 'kort')->expectsQuestion('Herhaal wachtwoord', 'anders')
        ->assertExitCode(1);
    $this->assertDatabaseCount('gebruikers',0);
});
