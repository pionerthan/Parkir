<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetugasController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:petugas'])
    ->prefix('petugas')
    ->as('petugas.')
    ->group(function () {

        Route::get('/', [PetugasController::class, 'index'])
            ->name('dashboard');

        Route::post('/checkin', [PetugasController::class, 'checkin'])
            ->name('checkin');

        Route::post('/checkout', [PetugasController::class, 'checkout'])
            ->name('checkout');
    });
