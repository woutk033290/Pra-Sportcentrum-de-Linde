<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

// Inloggen beheerder
Route::get('/login', [LoginController::class, 'toon'])->name('login');
Route::post('/login', [LoginController::class, 'verwerken'])
    ->middleware('throttle:5,1')
    ->name('login.verwerken');

// Beheergedeelte: alleen bereikbaar als beheerder
Route::middleware('auth:beheer')
    ->prefix('beheer')
    ->name('beheer.')
    ->group(function () {
        Route::get('/', function () {
            return view('beheer.dashboard');
        })->name('dashboard');

        Route::post('/uitloggen', [LoginController::class, 'uitloggen'])->name('uitloggen');

        // Hier komen de overzichten (lessen, leden, reserveringen, ...)
    });