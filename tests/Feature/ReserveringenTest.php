<?php

use App\Models\Reservering;
use Database\Factories\GebruikerFactory;
use Database\Factories\GroepslesFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);
beforeEach(function (): void {
    $this->withoutVite();
});

test('een reservering verschijnt alleen in het overzicht van het eigen lid', function () {
    $lid = GebruikerFactory::new()->create();
    $ander = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create(['naam' => 'Mijn spinningles']);

    $this->actingAs($lid)->post(route('les.aanmelden', $les))->assertSessionHas('succes');
    $this->get(route('reserveringen.index'))->assertSee('Mijn spinningles');
    $this->actingAs($ander)->get(route('reserveringen.index'))->assertDontSee('Mijn spinningles')->assertSee('Je hebt nog geen reserveringen.');
    $this->assertDatabaseHas('reserveringen', ['gebruiker_id' => $lid->id, 'groepsles_id' => $les->id, 'status' => 'bevestigd']);
});

test('volle lessen en dubbele reserveringen worden geweigerd', function () {
    $lid = GebruikerFactory::new()->create();
    $ander = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create(['maximaal_aantal_deelnemers' => 2]);
    $this->actingAs($lid)->post(route('les.aanmelden', $les));

    $this->post(route('les.aanmelden', $les))->assertSessionHas('fout', 'Je bent al aangemeld voor deze les.');
    $les->update(['maximaal_aantal_deelnemers' => 1]);
    $this->actingAs($ander)->post(route('les.aanmelden', $les))->assertSessionHas('fout', 'Deze les is vol.');
    $this->assertDatabaseCount('reserveringen', 1);
});

test('annuleren is toegestaan tot exact twee uur vooraf en maakt de plek vrij', function (int $seconden) {
    $this->travelTo(now()->startOfSecond());
    $lid = GebruikerFactory::new()->create();
    $ander = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create(['starttijd' => now()->addSeconds($seconden), 'maximaal_aantal_deelnemers' => 1]);
    $this->actingAs($lid)->post(route('les.aanmelden', $les));
    $reservering = Reservering::sole();

    $this->delete(route('reserveringen.destroy', $reservering))->assertRedirectToRoute('reserveringen.index')->assertSessionHas('status');
    $this->assertDatabaseHas('reserveringen', ['id' => $reservering->id, 'status' => 'geannuleerd']);
    $this->get(route('reserveringen.index'))->assertSee('Je hebt nog geen reserveringen.');
    $this->actingAs($ander)->post(route('les.aanmelden', $les))->assertSessionHas('succes');
    $this->assertDatabaseHas('reserveringen', ['gebruiker_id' => $ander->id, 'status' => 'bevestigd']);
})->with(['meer dan twee uur' => 7201, 'exact twee uur' => 7200]);

test('binnen twee uur blijft de reservering staan met een Nederlandse melding', function () {
    $this->travelTo(now()->startOfSecond());
    $lid = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create(['starttijd' => now()->addSeconds(7199)]);
    $this->actingAs($lid)->post(route('les.aanmelden', $les));
    $reservering = Reservering::sole();

    $this->from(route('reserveringen.index'))->delete(route('reserveringen.destroy', $reservering))
        ->assertInvalid(['reservering' => 'Annuleren kan tot uiterlijk 2 uur voor aanvang.']);
    $this->get(route('reserveringen.index'))->assertSee('Annuleren kan tot uiterlijk 2 uur voor aanvang.');
    $this->assertDatabaseHas('reserveringen', ['id' => $reservering->id, 'status' => 'bevestigd', 'geannuleerd_op' => null]);
});

test('een lid kan geen reservering van een ander annuleren', function () {
    $lid = GebruikerFactory::new()->create();
    $ander = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create();
    $this->actingAs($lid)->post(route('les.aanmelden', $les));
    $reservering = Reservering::sole();

    $this->actingAs($ander)->delete(route('reserveringen.destroy', $reservering))->assertForbidden();
    $this->assertDatabaseHas('reserveringen', ['id' => $reservering->id, 'status' => 'bevestigd']);
});

test('opnieuw reserveren na annuleren hergebruikt de reservering', function () {
    $lid = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create();
    $this->actingAs($lid)->post(route('les.aanmelden', $les));
    $reservering = Reservering::sole();
    $this->delete(route('reserveringen.destroy', $reservering));
    $this->delete(route('reserveringen.destroy', $reservering))->assertInvalid(['reservering' => 'Deze reservering is al geannuleerd.']);

    $this->post(route('les.aanmelden', $les))->assertSessionHas('succes');
    $this->assertDatabaseCount('reserveringen', 1);
    $this->assertDatabaseHas('reserveringen', ['id' => $reservering->id, 'status' => 'bevestigd', 'geannuleerd_op' => null]);
});

test('bezoekers worden naar inloggen gestuurd', function () {
    $les = GroepslesFactory::new()->create();
    $this->get(route('reserveringen.index'))->assertRedirectToRoute('login');
    $this->post(route('les.aanmelden', $les))->assertRedirectToRoute('login');
    $this->delete(route('reserveringen.destroy', 1))->assertRedirectToRoute('login');
    $this->assertDatabaseCount('reserveringen', 0);
});

test('trainers hebben geen toegang tot ledenreserveringen', function () {
    $trainer = GebruikerFactory::new()->trainer()->create();
    $les = GroepslesFactory::new()->create();
    $this->actingAs($trainer)->get(route('reserveringen.index'))->assertForbidden();
    $this->post(route('les.aanmelden', $les))->assertSessionHas('fout');
    $this->assertDatabaseCount('reserveringen', 0);
});
