<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoosterController;
use App\Http\Controllers\BeheerLesController;

Route::get('/', function () {
    return view('index');
});

Route::get('/rooster', [RoosterController::class, 'index'])->name('rooster');

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
        Route::get('/lessen', [BeheerLesController::class, 'index'])->name('lessen');
        Route::post('/lessen', [BeheerLesController::class, 'opslaan'])->name('lessen.opslaan');
        // Hier komen de overzichten (lessen, leden, reserveringen, ...)
    });