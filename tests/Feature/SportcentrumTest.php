<?php

use App\Models\Accounttoken;
use App\Models\Gebruiker;
use App\Models\Groepsles;
use App\Models\Reservering;
use App\Notifications\AccountActivatie;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);
beforeEach(function (): void {
    $this->withoutVite();
    $this->freezeTime();
});

test('public pages and empty schedules render', function () {
    foreach (['/', '/rooster', '/login', '/registreren'] as $path) {
        $this->get($path)->assertOk();
    }
    $this->get('/rooster')->assertSee('Er zijn geen lessen');
});
test('registration creates only a member and reports Dutch errors', function () {
    $this->post('/registreren', [])->assertInvalid(['naam' => 'verplicht', 'email' => 'verplicht', 'password' => 'verplicht']);
    $this->post('/registreren', ['naam' => 'Nieuw Lid', 'email' => 'nieuw@example.com', 'password' => 'veiligwachtwoord', 'password_confirmation' => 'veiligwachtwoord', 'rol' => 'beheerder'])->assertRedirectToRoute('login');
    $this->assertDatabaseHas('gebruikers', ['email' => 'nieuw@example.com', 'rol' => 'lid']);
    expect(Hash::check('veiligwachtwoord', Gebruiker::where('email', 'nieuw@example.com')->first()->password))->toBeTrue();
    $this->post('/registreren', ['naam' => 'Dubbel', 'email' => 'nieuw@example.com', 'password' => 'veiligwachtwoord', 'password_confirmation' => 'veiligwachtwoord'])->assertInvalid(['email' => 'al in gebruik']);
});
test('valid credentials sign each role in and logout removes access', function (string $rol, string $route) {
    $user = Gebruiker::factory()->create(['rol' => $rol]);
    $this->post('/login', ['email' => $user->email, 'password' => 'wachtwoord'])->assertRedirectToRoute($route);
    $this->assertAuthenticatedAs($user);
    $this->post('/uitloggen')->assertRedirectToRoute('home');
    $this->assertGuest();
    $this->get(route($route))->assertRedirectToRoute('login');
})->with([['lid', 'reserveringen.index'], ['trainer', 'trainer'], ['beheerder', 'beheer.dashboard']]);
test('invalid or inactive accounts cannot log in', function (bool $actief) {
    $user = Gebruiker::factory()->create(['geactiveerd_op' => $actief ? now() : null]);
    $this->post('/login', ['email' => $user->email, 'password' => $actief ? 'fout' : 'wachtwoord'])->assertInvalid(['email' => 'onjuist']);
    $this->assertGuest();
})->with([true, false]);
test('roles cannot access other role areas', function (string $rol, string $path) {
    $this->actingAs(Gebruiker::factory()->create(['rol' => $rol]))->get($path)->assertForbidden();
})->with([['lid', '/beheer'], ['lid', '/trainer'], ['trainer', '/beheer'], ['beheerder', '/trainer'], ['trainer', '/reserveringen']]);
test('visitors must log in before protected actions', function () {
    $les = Groepsles::factory()->create();
    $this->post(route('reserveringen.store', $les))->assertRedirectToRoute('login');
    $this->get('/beheer')->assertRedirectToRoute('login');
    $this->get('/trainer')->assertRedirectToRoute('login');
    $this->assertDatabaseCount('reserveringen', 0);
});
test('day filter shows only matching lessons and safely escapes content', function () {
    $les = Groepsles::factory()->create(['naam' => 'Yoga <script>alert(1)</script>', 'starttijd' => now()->addDay()->setTime(10, 0), 'eindtijd' => now()->addDay()->setTime(11, 0)]);
    Groepsles::factory()->create(['naam' => 'Andere dag', 'starttijd' => now()->addDays(2)]);
    $this->get(route('rooster', ['dag' => $les->starttijd->toDateString()]))->assertSee($les->naam)->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('Andere dag')->assertSee('20 vrije plekken')->assertSee('10:00')->assertSee('11:00');
    $this->get(route('rooster', ['dag' => now()->addDays(5)->toDateString()]))->assertSee('Er zijn geen lessen')->assertSee('Filter wissen');
    $this->get('/rooster?dag=invalid')->assertInvalid('dag');
});
test('booking updates capacity and rejects duplicates and full lessons', function () {
    $les = Groepsles::factory()->create(['maximaal_aantal_deelnemers' => 1]);
    $lid = Gebruiker::factory()->create();
    $this->actingAs($lid)->post(route('reserveringen.store', $les))->assertRedirectToRoute('reserveringen.index');
    $this->assertDatabaseHas('reserveringen', ['groepsles_id' => $les->id, 'gebruiker_id' => $lid->id, 'status' => 'bevestigd']);
    $this->get('/reserveringen')->assertSee($les->naam);
    $this->get('/rooster')->assertSee('Vol · 0 vrije plekken');
    $this->post(route('reserveringen.store', $les))->assertInvalid(['reservering' => 'al gereserveerd']);
    $this->actingAs(Gebruiker::factory()->create())->post(route('reserveringen.store', $les))->assertInvalid(['reservering' => 'vol']);
    $this->assertDatabaseCount('reserveringen', 1);
});
test('past and removed lessons cannot be booked', function (string $status, int $uren) {
    $les = Groepsles::factory()->create(['status' => $status, 'starttijd' => now()->addHours($uren)]);
    $this->actingAs(Gebruiker::factory()->create())->post(route('reserveringen.store', $les))->assertInvalid(['reservering' => 'niet meer beschikbaar']);
    $this->assertDatabaseCount('reserveringen', 0);
})->with([['gepland', -1], ['geannuleerd', 24]]);
test('cancellation permits exactly two hours and releases capacity', function () {
    $les = Groepsles::factory()->create(['starttijd' => now()->addHours(2), 'maximaal_aantal_deelnemers' => 1]);
    $lid = Gebruiker::factory()->create();
    $this->actingAs($lid)->post(route('reserveringen.store', $les));
    $reservering = Reservering::firstOrFail();
    $this->delete(route('reserveringen.destroy', $reservering))->assertRedirect();
    $this->assertDatabaseHas('reserveringen', ['id' => $reservering->id, 'status' => 'geannuleerd']);
    $this->get('/rooster')->assertSee('1 vrije plekken');
    $this->post(route('reserveringen.store', $les))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('reserveringen', 1);
    $this->assertDatabaseHas('reserveringen', ['id' => $reservering->id, 'status' => 'bevestigd']);
});
test('late cancellation and cancelling another members booking are rejected', function () {
    $les = Groepsles::factory()->create(['starttijd' => now()->addHours(2)->subSecond()]);
    $lid = Gebruiker::factory()->create();
    $this->actingAs($lid)->post(route('reserveringen.store', $les));
    $reservering = Reservering::firstOrFail();
    $this->delete(route('reserveringen.destroy', $reservering))->assertInvalid(['reservering' => '2 uur']);
    $this->actingAs(Gebruiker::factory()->create())->delete(route('reserveringen.destroy', $reservering))->assertForbidden();
    $this->assertDatabaseHas('reserveringen', ['id' => $reservering->id, 'status' => 'bevestigd']);
});
test('administrator can create and update a lesson and trainer views follow reassignment', function () {
    $les = Groepsles::factory()->create();
    $admin = Gebruiker::factory()->beheerder()->create();
    $trainer = Gebruiker::factory()->trainer()->create();
    $data = ['naam' => 'Nieuwe les', 'beschrijving' => 'Voor iedereen', 'starttijd' => now()->addDays(3)->format('Y-m-d\\TH:i'), 'eindtijd' => now()->addDays(3)->addHour()->format('Y-m-d\\TH:i'), 'maximaal_aantal_deelnemers' => 8, 'trainer_id' => $les->trainer_id, 'locatie_id' => $les->locatie_id, 'lestype_id' => $les->lestype_id];
    $this->actingAs($admin)->get('/beheer/lessen/nieuw')->assertOk();
    $this->post(route('beheer.lessen.store'), $data)->assertRedirectToRoute('beheer.dashboard');
    $nieuw = Groepsles::where('naam', 'Nieuwe les')->firstOrFail();
    $this->get(route('beheer.lessen.edit', $nieuw))->assertSee('Nieuwe les');
    $this->actingAs($les->trainer)->get('/trainer')->assertSee('Nieuwe les');
    $this->actingAs($admin)->put(route('beheer.lessen.update', $nieuw), [...$data, 'trainer_id' => $trainer->id, 'naam' => 'Aangepaste les'])->assertSessionHasNoErrors();
    $this->actingAs($trainer)->get('/trainer')->assertSee('Aangepaste les')->assertDontSee($les->naam);
    $this->actingAs($les->trainer)->get('/trainer')->assertDontSee('Aangepaste les');
    $this->assertDatabaseHas('groepslessen', ['id' => $nieuw->id, 'trainer_id' => $trainer->id, 'naam' => 'Aangepaste les']);
});
test('invalid lesson data and lowering capacity preserve the original lesson', function () {
    $les = Groepsles::factory()->create(['maximaal_aantal_deelnemers' => 2]);
    foreach (range(1, 2) as $i) {
        $this->actingAs(Gebruiker::factory()->create())->post(route('reserveringen.store', $les));
    }
    $this->actingAs(Gebruiker::factory()->beheerder()->create());
    $data = ['naam' => 'Wijziging', 'starttijd' => $les->starttijd->format('Y-m-d\\TH:i'), 'eindtijd' => $les->eindtijd->format('Y-m-d\\TH:i'), 'maximaal_aantal_deelnemers' => 1, 'trainer_id' => $les->trainer_id, 'locatie_id' => $les->locatie_id, 'lestype_id' => $les->lestype_id];
    $this->put(route('beheer.lessen.update', $les), $data)->assertInvalid(['maximaal_aantal_deelnemers' => 'niet lager']);
    $this->put(route('beheer.lessen.update', $les), [...$data, 'eindtijd' => $data['starttijd'], 'trainer_id' => Gebruiker::factory()->create()->id])->assertInvalid(['eindtijd', 'trainer_id']);
    $this->assertDatabaseHas('groepslessen', ['id' => $les->id, 'naam' => $les->naam, 'maximaal_aantal_deelnemers' => 2]);
    $this->post(route('beheer.lessen.store'), [])->assertInvalid(['naam' => 'verplicht', 'starttijd' => 'verplicht', 'trainer_id' => 'verplicht']);
});
test('administrator removes one participant without changing other bookings', function () {
    $les = Groepsles::factory()->create(['starttijd' => now()->addHour(), 'maximaal_aantal_deelnemers' => 2]);
    foreach (range(1, 2) as $i) {
        $this->actingAs(Gebruiker::factory()->create())->post(route('reserveringen.store', $les));
    }
    $reserveringen = Reservering::get();
    $this->actingAs(Gebruiker::factory()->beheerder()->create())->get(route('beheer.deelnemers', $les))->assertSee('0 vrije plekken')->assertSee('data-confirm', false);
    $this->delete(route('beheer.reserveringen.destroy', $reserveringen[0]))->assertRedirect();
    $this->assertDatabaseHas('reserveringen', ['id' => $reserveringen[0]->id, 'status' => 'geannuleerd']);
    $this->assertDatabaseHas('reserveringen', ['id' => $reserveringen[1]->id, 'status' => 'bevestigd']);
    $this->get(route('beheer.deelnemers', $les))->assertSee('1 vrije plekken');
});
test('removing a lesson removes it from public and trainer schedules and cancels reservations', function () {
    $les = Groepsles::factory()->create();
    $this->actingAs(Gebruiker::factory()->create())->post(route('reserveringen.store', $les));
    $this->actingAs(Gebruiker::factory()->beheerder()->create())->get('/beheer')->assertSee('data-confirm', false);
    $this->delete(route('beheer.lessen.destroy', $les))->assertRedirectToRoute('beheer.dashboard');
    $this->get('/rooster')->assertDontSee($les->naam);
    $this->actingAs($les->trainer)->get('/trainer')->assertDontSee($les->naam);
    $this->assertDatabaseHas('reserveringen', ['groepsles_id' => $les->id, 'status' => 'geannuleerd']);
});
test('colleague invitations are Dutch single use and allow setting a password', function () {
    Notification::fake();
    $this->actingAs(Gebruiker::factory()->beheerder()->create())->post(route('beheer.collega.store'), ['naam' => 'Nieuwe Trainer', 'email' => 'trainer@voorbeeld.nl', 'rol' => 'trainer'])->assertSessionHasNoErrors();
    $collega = Gebruiker::where('email', 'trainer@voorbeeld.nl')->firstOrFail();
    expect($collega->password)->toBeNull();
    $token = null;
    Notification::assertSentTo($collega, AccountActivatie::class, function ($notification) use (&$token, $collega) {
        $token = $notification->token;
        expect($notification->toMail($collega)->subject)->toBe('Activeer je account bij De Linde');

        return true;
    });
    $this->post('/uitloggen');
    $this->get(route('activatie', $token))->assertSee('Activeer je account');
    $this->post(route('activatie.store', $token), ['password' => 'nieuwgeheim', 'password_confirmation' => 'nieuwgeheim'])->assertRedirectToRoute('login');
    $this->assertDatabaseHas('gebruikers', ['id' => $collega->id, 'rol' => 'trainer']);
    expect(Hash::check('nieuwgeheim', $collega->fresh()->password))->toBeTrue();
    $this->get(route('activatie', $token))->assertStatus(410);
    $this->post('/login', ['email' => $collega->email, 'password' => 'nieuwgeheim'])->assertRedirectToRoute('trainer');
});
test('expired activation links cannot activate accounts', function () {
    $collega = Gebruiker::factory()->trainer()->create(['password' => null, 'geactiveerd_op' => null]);
    Accounttoken::create(['gebruiker_id' => $collega->id, 'token_hash' => hash('sha256', 'verlopen'), 'doel' => 'activatie', 'vervalt_op' => now()->subSecond()]);
    $this->get(route('activatie', 'verlopen'))->assertStatus(410);
    $this->post(route('activatie.store', 'verlopen'), ['password' => 'nieuwgeheim', 'password_confirmation' => 'nieuwgeheim'])->assertStatus(410);
    expect($collega->fresh()->geactiveerd_op)->toBeNull();
});
test('members and trainers cannot mutate administration resources', function (string $rol) {
    Notification::fake();
    $user = Gebruiker::factory()->create(['rol' => $rol]);
    $les = Groepsles::factory()->create();
    $this->actingAs($user)->post(route('beheer.collega.store'), ['naam' => 'Hacker', 'email' => 'extra@example.com', 'rol' => 'beheerder'])->assertForbidden();
    $this->post(route('beheer.lessen.store'), [])->assertForbidden();
    $this->put(route('beheer.lessen.update', $les), [])->assertForbidden();
    $this->delete(route('beheer.lessen.destroy', $les))->assertForbidden();
    $this->get(route('beheer.deelnemers', $les))->assertForbidden();
    Notification::assertNothingSent();
    $this->assertDatabaseHas('groepslessen', ['id' => $les->id, 'status' => 'gepland']);
})->with(['lid', 'trainer']);
test('colleague validation rejects missing duplicate and member role input', function () {
    Notification::fake();
    $admin = Gebruiker::factory()->beheerder()->create();
    $this->actingAs($admin)->post(route('beheer.collega.store'), [])->assertInvalid(['naam' => 'verplicht', 'email' => 'verplicht', 'rol' => 'verplicht']);
    $this->post(route('beheer.collega.store'), ['naam' => 'Collega', 'email' => $admin->email, 'rol' => 'lid'])->assertInvalid(['email' => 'al in gebruik', 'rol' => 'geldige']);
    Notification::assertNothingSent();
    $this->assertDatabaseCount('gebruikers', 1);
});
