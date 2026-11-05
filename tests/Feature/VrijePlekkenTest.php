<?php

use App\Models\Reservering;
use Database\Factories\GebruikerFactory;
use Database\Factories\GroepslesFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);
beforeEach(function (): void {
    $this->withoutVite();
    $this->travelTo('2027-01-04 08:00:00');
});

test('vrije plekken worden bijgewerkt na reserveren en annuleren', function (string $pagina) {
    $lid = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create(['maximaal_aantal_deelnemers' => 2]);
    $url = $pagina === 'les.tonen' ? route($pagina, $les) : route($pagina);
    $this->actingAs($lid)->get($url)->assertOk()->assertSee($pagina === 'les.tonen' ? 'Nog 2 van 2 plekken vrij' : '2 plekken vrij');

    $this->post(route('les.aanmelden', $les))->assertSessionHas('succes');
    $this->get($url)->assertSee($pagina === 'les.tonen' ? 'Nog 1 van 2 plekken vrij' : '1 plek vrij');
    $this->post(route('les.aanmelden', $les))->assertSessionHas('fout');
    $this->get($url)->assertSee($pagina === 'les.tonen' ? 'Nog 1 van 2 plekken vrij' : '1 plek vrij');
    $this->delete(route('reserveringen.destroy', Reservering::sole()))->assertSessionHas('status');
    $this->get($url)->assertSee($pagina === 'les.tonen' ? 'Nog 2 van 2 plekken vrij' : '2 plekken vrij');
})->with(['home', 'rooster', 'les.tonen']);

test('de laatste reservering toont nul vrije plekken en een volmelding', function (string $pagina) {
    $lid = GebruikerFactory::new()->create();
    $les = GroepslesFactory::new()->create(['maximaal_aantal_deelnemers' => 1]);
    $url = $pagina === 'les.tonen' ? route($pagina, $les) : route($pagina);

    $this->actingAs($lid)->post(route('les.aanmelden', $les))->assertSessionHas('succes');
    $this->get($url)->assertSee('0 plekken vrij')->assertSee($pagina === 'les.tonen' ? 'Deze les is vol' : 'Vol');
})->with(['home', 'rooster', 'les.tonen']);

test('elke les toont zijn eigen aantal vrije plekken', function () {
    $les = GroepslesFactory::new()->create(['naam' => 'Spinning ochtend', 'maximaal_aantal_deelnemers' => 3, 'starttijd' => '2027-01-05 09:00:00']);
    GroepslesFactory::new()->create(['naam' => 'Yoga ochtend', 'maximaal_aantal_deelnemers' => 8, 'starttijd' => '2027-01-05 10:00:00']);
    $lid = GebruikerFactory::new()->create();
    $this->actingAs($lid)->post(route('les.aanmelden', $les));

    $this->get(route('rooster'))->assertSeeInOrder(['Spinning ochtend', '2 plekken vrij', 'Yoga ochtend', '8 plekken vrij']);
    $this->get(route('home'))->assertSeeInOrder(['Spinning ochtend', '2 plekken vrij', 'Yoga ochtend', '8 plekken vrij']);
});

test('een geannuleerde les wordt niet als beschikbaar aangeboden', function (string $pagina) {
    $les = GroepslesFactory::new()->create(['status' => 'geannuleerd']);
    $url = $pagina === 'les.tonen' ? route($pagina, $les) : route($pagina);

    $this->get($url)->assertSee($pagina === 'les.tonen' ? 'Deze les is geannuleerd.' : 'Geannuleerd')->assertDontSee('plekken vrij');
})->with(['home', 'rooster', 'les.tonen']);
