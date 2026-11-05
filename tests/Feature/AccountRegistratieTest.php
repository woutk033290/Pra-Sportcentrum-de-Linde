<?php

use App\Models\Gebruiker;
use Database\Factories\GebruikerFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

function registratieGegevens(array $overrides = []): array
{
    return array_replace([
        'voornaam' => 'Anna', 'achternaam' => 'de Vries',
        'email' => 'anna@example.com', 'password' => 'veiligwachtwoord',
        'password_confirmation' => 'veiligwachtwoord', 'voorwaarden' => '1',
    ], $overrides);
}

test('bezoekers kunnen vanuit de login het registratieformulier openen', function () {
    $this->get('/login')->assertSee(route('registreren'));
    $this->get('/registreren')->assertOk()->assertSee('Account aanmaken')->assertSee('Herhaal wachtwoord');
});

test('registratie maakt een actief lid met een gehasht wachtwoord dat kan inloggen', function () {
    $this->post('/registreren', registratieGegevens(['rol' => 'beheerder']))
        ->assertRedirectToRoute('login')->assertSessionHas('succes');

    $gebruiker = Gebruiker::where('email', 'anna@example.com')->firstOrFail();
    expect($gebruiker->rol)->toBe('lid');
    expect(Hash::check('veiligwachtwoord', $gebruiker->password))->toBeTrue();
    expect($gebruiker->geactiveerd_op)->not->toBeNull();
    expect($gebruiker->voorwaarden_akkoord_op)->not->toBeNull();
    $this->assertGuest('web');
    $this->post('/login', ['email' => 'anna@example.com', 'password' => 'veiligwachtwoord'])->assertRedirectToRoute('rooster');
    $this->assertAuthenticatedAs($gebruiker, 'web');
    $this->assertGuest('beheer');
    $this->get('/beheer')->assertRedirectToRoute('login');
    $this->post('/uitloggen')->assertRedirectToRoute('login');
    $this->assertGuest('web');
});

test('ontbrekende velden geven Nederlandse foutmeldingen zonder account aan te maken', function () {
    $this->post('/registreren', [])->assertInvalid([
        'voornaam' => 'Voornaam is verplicht.', 'achternaam' => 'Achternaam is verplicht.',
        'email' => 'E-mailadres is verplicht.', 'password' => 'Wachtwoord is verplicht.',
        'voorwaarden' => 'Ga akkoord met de voorwaarden.',
    ]);
    $this->assertDatabaseCount('gebruikers', 0);
});

test('een bestaand e-mailadres wordt geweigerd', function () {
    GebruikerFactory::new()->create(['email' => 'anna@example.com']);
    $this->post('/registreren', registratieGegevens())->assertInvalid(['email' => 'Dit e-mailadres is al in gebruik.']);
    $this->assertDatabaseCount('gebruikers', 1);
});

test('ongeldige registratiegegevens worden in het Nederlands afgewezen', function (array $invoer, string $veld, string $melding) {
    $this->post('/registreren', registratieGegevens($invoer))->assertInvalid([$veld => $melding]);
    $this->assertDatabaseCount('gebruikers', 0);
})->with([
    'ongeldig email' => [['email' => 'ongeldig'], 'email', 'Vul een geldig e-mailadres in.'],
    'kort wachtwoord' => [['password' => 'kort', 'password_confirmation' => 'kort'], 'password', 'Het wachtwoord moet minimaal 8 tekens bevatten.'],
    'andere bevestiging' => [['password_confirmation' => 'anders'], 'password', 'De wachtwoorden komen niet overeen.'],
    'geen bevestiging' => [['password_confirmation' => ''], 'password', 'De wachtwoorden komen niet overeen.'],
    'geen akkoord' => [['voorwaarden' => '0'], 'voorwaarden', 'Ga akkoord met de voorwaarden.'],
    'ongeldige datum' => [['geboortedatum' => 'geen datum'], 'geboortedatum', 'Vul een geldige geboortedatum in.'],
    'toekomstige datum' => [['geboortedatum' => '2999-01-01'], 'geboortedatum', 'De geboortedatum mag niet in de toekomst liggen.'],
    'lange naam' => [['voornaam' => str_repeat('a', 256)], 'voornaam', 'Voornaam mag maximaal 255 tekens bevatten.'],
    'lang wachtwoord' => [['password' => str_repeat('a', 73)], 'password', 'Wachtwoord mag maximaal 72 tekens bevatten.'],
]);

test('optionele profielgegevens worden opgeslagen', function () {
    $this->post('/registreren', registratieGegevens(['telefoonnummer' => '0612345678', 'geboortedatum' => '2000-01-01']))->assertRedirectToRoute('login');
    $this->assertDatabaseHas('gebruikers', ['voornaam' => 'Anna', 'achternaam' => 'de Vries', 'telefoonnummer' => '0612345678', 'geboortedatum' => '2000-01-01']);
});

test('bij een fout blijven naam en email behouden en wordt het wachtwoord niet teruggestuurd', function () {
    $this->from('/registreren')->post('/registreren', registratieGegevens(['voornaam' => '<script>alert(1)</script>', 'password_confirmation' => 'anders']))->assertRedirect('/registreren');
    $this->get('/registreren')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('anna@example.com')->assertDontSee('veiligwachtwoord');
});

test('bestaande beheerders blijven via de beheer guard inloggen', function () {
    $beheerder = GebruikerFactory::new()->beheerder()->create(['password' => Hash::make('veiligwachtwoord')]);
    $this->post('/login', ['email' => $beheerder->email, 'password' => 'veiligwachtwoord'])->assertRedirectToRoute('beheer.dashboard');
    $this->assertAuthenticatedAs($beheerder, 'beheer');
});

test('leden met ongeldige gegevens krijgen geen sessie', function (array $overrides, string $password) {
    $gebruiker = GebruikerFactory::new()->create(array_replace(['password' => Hash::make('veiligwachtwoord')], $overrides));
    $this->post('/login', ['email' => $gebruiker->email, 'password' => $password])->assertSessionHas('error', 'E-mailadres of wachtwoord is onjuist.');
    $this->assertGuest('web');
    $this->assertGuest('beheer');
})->with([
    'verkeerd wachtwoord' => [[], 'verkeerdwachtwoord'],
    'inactief lid' => [['geactiveerd_op' => null], 'veiligwachtwoord'],
    'verwijderd lid' => [['verwijderd_op' => '2026-01-01 00:00:00'], 'veiligwachtwoord'],
    'inactieve trainer' => [['rol' => 'trainer', 'geactiveerd_op' => null], 'veiligwachtwoord'],
]);
