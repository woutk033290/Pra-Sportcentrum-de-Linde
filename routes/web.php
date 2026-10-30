<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoosterController;
use App\Http\Controllers\BeheerLesController;
use App\Http\Controllers\LesController;
use App\Http\Controllers\ActiveerController;
use App\Http\Controllers\BeheerDashboardController;

Route::get('/', function () {
    return view('index');
});

Route::get('/rooster', [RoosterController::class, 'index'])->name('rooster');
Route::get('/rooster/{groepsles}', [LesController::class, 'tonen'])->name('les.tonen');
Route::post('/rooster/{groepsles}/aanmelden', [LesController::class, 'aanmelden'])->name('les.aanmelden');
Route::post('/rooster/{groepsles}/annuleren', [LesController::class, 'annuleren'])->name('les.annuleren');
Route::post('/rooster/{groepsles}/afmelden/{reservering}', [LesController::class, 'afmelden'])->name('les.afmelden');
// Inloggen beheerder
Route::get('/login', [LoginController::class, 'toon'])->name('login');
Route::post('/login', [LoginController::class, 'verwerken'])
    ->middleware('throttle:5,1')
    ->name('login.verwerken');

// Account activeren via uitnodigingslink
Route::get('/activeren/{token}', [ActiveerController::class, 'toon'])->name('account.activeren');
Route::post('/activeren/{token}', [ActiveerController::class, 'opslaan'])
    ->middleware('throttle:10,1')
    ->name('account.activeren.opslaan');
// Beheergedeelte: alleen bereikbaar als beheerder
Route::middleware('auth:beheer')
    ->prefix('beheer')
    ->name('beheer.')
    ->group(function () {
        Route::get('/', [BeheerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/collegas', [BeheerDashboardController::class, 'collegaOpslaan'])->name('collegas.opslaan');
        Route::post('/collegas/{gebruiker}/uitnodiging', [BeheerDashboardController::class, 'nieuweUitnodiging'])->name('collegas.uitnodiging');

        Route::post('/uitloggen', [LoginController::class, 'uitloggen'])->name('uitloggen');
        Route::get('/lessen', [BeheerLesController::class, 'index'])->name('lessen');
        Route::post('/lessen', [BeheerLesController::class, 'opslaan'])->name('lessen.opslaan');
        Route::get('/lessen/{groepsles}/bewerken', [BeheerLesController::class, 'bewerken'])->name('lessen.bewerken');
        Route::put('/lessen/{groepsles}', [BeheerLesController::class, 'bijwerken'])->name('lessen.bijwerken');
        Route::delete('/lessen/{groepsles}', [BeheerLesController::class, 'verwijderen'])->name('lessen.verwijderen');
        // Hier komen de overzichten (lessen, leden, reserveringen, ...)
    });