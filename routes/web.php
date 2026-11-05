<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BeheerDashboardController;
use App\Http\Controllers\BeheerLesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReserveringController;
use App\Http\Controllers\RoosterController;
use App\Http\Controllers\TrainerController;
use App\Http\Middleware\ControleerBeheerder;
use App\Http\Middleware\RequireRole;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Rooster en lesdetails
Route::get('/rooster', [RoosterController::class, 'index'])->name('rooster');
Route::get('/rooster/{groepsles}', [LesController::class, 'tonen'])->name('les.tonen');
Route::post('/rooster/{groepsles}/aanmelden', [LesController::class, 'aanmelden'])->name('les.aanmelden');
Route::post('/rooster/{groepsles}/annuleren', [LesController::class, 'annuleren'])->name('les.annuleren');
Route::post('/rooster/{groepsles}/afmelden/{reservering}', [LesController::class, 'afmelden'])->name('les.afmelden');

// Registreren (leden)
Route::view('/registreren', 'auth.registreren')->name('registreren');
Route::post('/registreren', [AccountController::class, 'register'])
    ->middleware('throttle:10,1')
    ->name('registreren.store');

// Inloggen en uitloggen
Route::get('/login', [LoginController::class, 'toon'])->name('login');
Route::post('/login', [LoginController::class, 'verwerken'])
    ->middleware('throttle:5,1')
    ->name('login.verwerken');
Route::post('/uitloggen', [LoginController::class, 'uitloggen'])->name('uitloggen');

// Beheergedeelte: alleen bereikbaar als beheerder
Route::middleware(['auth:beheer', ControleerBeheerder::class])
    ->prefix('beheer')
    ->name('beheer.')
    ->group(function () {
        Route::get('/', [BeheerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/collegas', [BeheerDashboardController::class, 'collegaOpslaan'])->name('collegas.opslaan');
        Route::delete('/collegas/{gebruiker}', [BeheerDashboardController::class, 'collegaVerwijderen'])->name('collegas.verwijderen');

        Route::post('/uitloggen', [LoginController::class, 'uitloggen'])->name('uitloggen');

        Route::get('/lessen', [BeheerLesController::class, 'index'])->name('lessen');
        Route::post('/lessen', [BeheerLesController::class, 'opslaan'])->name('lessen.opslaan');
        Route::get('/lessen/{groepsles}/bewerken', [BeheerLesController::class, 'bewerken'])->name('lessen.bewerken');
        Route::put('/lessen/{groepsles}', [BeheerLesController::class, 'bijwerken'])->name('lessen.bijwerken');
        Route::delete('/lessen/{groepsles}', [BeheerLesController::class, 'verwijderen'])->name('lessen.verwijderen');
    });

// Trainergedeelte
Route::middleware(['auth:web', RequireRole::class.':trainer'])->group(function () {
    Route::get('/trainer', [TrainerController::class, 'index'])
        ->name('trainer.dashboard');
});

Route::middleware(['auth:web', RequireRole::class.':lid'])->group(function () {
    Route::get('/mijn-reserveringen', [ReserveringController::class, 'index'])->name('reserveringen.index');
    Route::delete('/mijn-reserveringen/{reservering}', [ReserveringController::class, 'destroy'])->name('reserveringen.destroy');
});
