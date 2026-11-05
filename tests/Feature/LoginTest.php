<?php

use Database\Factories\GebruikerFactory;
use Database\Factories\GroepslesFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

test('een ingelogd lid kan een les reserveren', function () {
    $lid = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create();

    $this->post(route('login.verwerken'), ['email' => $lid->email, 'password' => 'wachtwoord'])
        ->assertRedirectToRoute('rooster');
    $this->assertAuthenticatedAs($lid, 'web');
    $this->get(route('les.tonen', $les))->assertOk()->assertSee('action="'.route('les.aanmelden', $les).'"', false);
    $this->post(route('les.aanmelden', $les))->assertRedirectToRoute('les.tonen', $les)->assertSessionHas('succes');
    $this->assertDatabaseHas('reserveringen', ['gebruiker_id' => $lid->id, 'groepsles_id' => $les->id, 'status' => 'bevestigd']);
});

test('onjuiste gegevens tonen een fout en geven geen toegang tot reserveren', function (string $email, string $password) {
    GebruikerFactory::new()->create(['email' => 'lid@example.com']);
    $les = GroepslesFactory::new()->create();

    $this->from(route('login'))->post(route('login.verwerken'), ['email' => $email, 'password' => $password])
        ->assertRedirectToRoute('login')
        ->assertSessionHas('error', 'E-mailadres of wachtwoord is onjuist.')
        ->assertSessionHasInput('email', $email)
        ->assertSessionMissingInput('password');
    $this->assertGuest('web');
    $this->assertGuest('beheer');
    $this->get(route('login'))->assertSee('E-mailadres of wachtwoord is onjuist.');
    $this->post(route('les.aanmelden', $les))->assertRedirectToRoute('login');
    $this->assertDatabaseCount('reserveringen', 0);
})->with([
    'verkeerd wachtwoord' => ['lid@example.com', 'verkeerd'],
    'onbekend email' => ['onbekend@example.com', 'wachtwoord'],
]);

test('uitloggen wist de sessie en blokkeert nieuwe reserveringen', function () {
    $lid = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create();
    $this->post(route('login.verwerken'), ['email' => $lid->email, 'password' => 'wachtwoord']);
    $this->withSession(['persoonlijke_data' => 'wissen']);

    $this->post(route('uitloggen'))->assertRedirectToRoute('login')->assertSessionMissing('persoonlijke_data');
    $this->assertGuest('web');
    $this->assertGuest('beheer');
    $this->get(route('les.tonen', $les))->assertOk()->assertDontSee('action="'.route('les.aanmelden', $les).'"', false);
    $this->post(route('les.aanmelden', $les))->assertRedirectToRoute('login');
    $this->assertDatabaseCount('reserveringen', 0);
});

test('een bezoeker kan niet rechtstreeks reserveren', function () {
    $les = GroepslesFactory::new()->create();

    $this->post(route('les.aanmelden', $les))->assertRedirectToRoute('login');
    $this->assertDatabaseCount('reserveringen', 0);
});

test('een ingelogd lid gaat vanaf de loginpagina naar het rooster', function () {
    $lid = GebruikerFactory::new()->create();

    $this->actingAs($lid, 'web')->get(route('login'))->assertRedirectToRoute('rooster');
});

test('ontbrekende loginvelden geven Nederlandse fouten', function () {
    $this->post(route('login.verwerken'), [])->assertInvalid([
        'email' => 'Vul je e-mailadres in.',
        'password' => 'Vul je wachtwoord in.',
    ]);
    $this->assertGuest('web');
});

test('ongeldige loginvelden worden gecontroleerd', function (array $gegevens, string $veld, string $melding) {
    $this->post(route('login.verwerken'), $gegevens)->assertInvalid([$veld => $melding]);
    $this->assertGuest('web');
})->with([
    'ongeldig email' => [['email' => 'geen-email', 'password' => 'wachtwoord'], 'email', 'Vul een geldig e-mailadres in.'],
    'wachtwoord als array' => [['email' => 'lid@example.com', 'password' => ['wachtwoord']], 'password', 'Vul een geldig wachtwoord in.'],
]);
