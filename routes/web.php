<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalitaController;
use App\Http\Controllers\KaderController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', function () {
    return view('welcome');
});


// =====================================================
// AUTH ROUTES
// =====================================================

// AUTH ROUTES
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

Route::get('/registrasi', [AuthController::class, 'showRegister'])
    ->name('registrasi');

Route::post('/registrasi', [AuthController::class, 'register'])
    ->name('registrasi.post');


// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


// =====================================================
// ORANG TUA
// Role database: orang_tua
// =====================================================

Route::middleware(['auth', 'role:orang_tua'])
    ->prefix('orangtua')
    ->name('orangtua.')
    ->group(function () {

        Route::get('/anakku', function () {
            return view('orangtua.anakku');
        })->name('anakku');

    });


// =====================================================
// KADER
// Role database: kader
// =====================================================

Route::middleware(['auth', 'role:kader'])
    ->prefix('kader')
    ->name('kader.')
    ->group(function () {

        Route::get('/dashboard', [KaderController::class, 'dashboard'])
            ->name('dashboard');

        Route::resource('balita', BalitaController::class);

    });
