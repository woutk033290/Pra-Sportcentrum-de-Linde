<?php

use Database\Factories\GebruikerFactory;
use Database\Factories\GroepslesFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);
beforeEach(function (): void {
    $this->withoutVite();
});

test('trainers kunnen inloggen en zien alleen hun eigen lessen op volgorde', function () {
    $trainer = GebruikerFactory::new()->trainer()->create();
    GroepslesFactory::new()->create(['trainer_id' => $trainer->id, 'naam' => 'Late les', 'starttijd' => '2027-01-02 18:00:00', 'eindtijd' => '2027-01-02 19:00:00']);
    GroepslesFactory::new()->create(['trainer_id' => $trainer->id, 'naam' => 'Vroege les', 'starttijd' => '2027-01-02 09:00:00', 'eindtijd' => '2027-01-02 10:00:00']);
    GroepslesFactory::new()->create(['naam' => 'Les van iemand anders']);

    $this->post(route('login.verwerken'), ['email' => $trainer->email, 'password' => 'wachtwoord'])->assertRedirectToRoute('trainer.dashboard');
    $this->get(route('trainer.dashboard'))->assertOk()->assertSeeInOrder(['Vroege les', 'Late les'])->assertSee('02-01-2027')->assertSee('09:00')->assertSee('10:00')->assertDontSee('Les van iemand anders');
    $this->get(route('login'))->assertRedirectToRoute('trainer.dashboard');
});

test('roosterwijzigingen en gewijzigde trainerkoppelingen verschijnen direct', function () {
    $trainer = GebruikerFactory::new()->trainer()->create();
    $ander = GebruikerFactory::new()->trainer()->create();
    $les = GroepslesFactory::new()->create(['trainer_id' => $trainer->id, 'naam' => 'Oude naam']);
    $this->actingAs($trainer)->get(route('trainer.dashboard'))->assertSee('Oude naam');
    $les->update(['naam' => 'Nieuwe naam', 'starttijd' => '2027-02-03 14:00:00', 'eindtijd' => '2027-02-03 15:00:00', 'status' => 'geannuleerd']);

    $this->get(route('trainer.dashboard'))->assertSee('Nieuwe naam')->assertSee('03-02-2027')->assertSee('14:00')->assertSee('15:00')->assertSee('Geannuleerd')->assertDontSee('Oude naam');
    $les->update(['trainer_id' => $ander->id]);
    $this->get(route('trainer.dashboard'))->assertDontSee('Nieuwe naam')->assertSee('Er zijn nog geen lessen aan jou gekoppeld.');
});

test('bezoekers en andere rollen mogen het traineroverzicht niet openen', function () {
    $this->get(route('trainer.dashboard'))->assertRedirectToRoute('login');
    $lid = GebruikerFactory::new()->create();
    $this->actingAs($lid)->get(route('trainer.dashboard'))->assertForbidden();
    $beheerder = GebruikerFactory::new()->beheerder()->create();
    $this->actingAs($beheerder)->get(route('trainer.dashboard'))->assertForbidden();
});

test('inactieve en verwijderde trainers krijgen geen toegang', function (array $status) {
    $trainer = GebruikerFactory::new()->trainer()->create($status);
    $this->actingAs($trainer)->get(route('trainer.dashboard'))->assertForbidden();
})->with([
    'inactief' => [['geactiveerd_op' => null]],
    'verwijderd' => [['verwijderd_op' => '2026-01-01 00:00:00']],
]);
