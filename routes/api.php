<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BalitaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EdukasiController;
use App\Http\Controllers\Api\JadwalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Prevanta (Mobile)
|--------------------------------------------------------------------------
| Setiap controller di folder App\Http\Controllers\Api merefleksikan
| controller dan fungsi halaman yang ada di web (Single Responsibility).
|
| Base URL: /api/...
|--------------------------------------------------------------------------
*/

// =========================================================
// [PUBLIC] Health check
// =========================================================
Route::get('/ping', fn() => response()->json(['status' => 'ok', 'app' => 'Prevanta API']));

// =========================================================
// AUTHENTICATION (PUBLIC)
// =========================================================
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

// =========================================================
// AUTHENTICATED (SANCTUM)
// =========================================================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =========================================================
    // BALITA & MONITORING (Mengikuti BalitaController web)
    // =========================================================
    Route::get('/monitoring-balita', [BalitaController::class, 'index'])->name('monitoring-balita');
    Route::get('/orang-tua', [BalitaController::class, 'orangTuaList'])->name('balita.orang-tua');
    Route::apiResource('balita', BalitaController::class)->parameters(['balita' => 'id']);

    // =========================================================
    // JADWAL (Mengikuti JadwalController web)
    // =========================================================
    Route::apiResource('jadwal', JadwalController::class)->parameters(['jadwal' => 'id']);

    // =========================================================
    // EDUKASI (Mengikuti EdukasiController web)
    // =========================================================
    Route::get('/edukasi/kategori', [EdukasiController::class, 'kategoriList'])->name('edukasi.kategori');
    Route::apiResource('edukasi', EdukasiController::class)->parameters(['edukasi' => 'id']);
});

// Route::apiResource('dashboard', DashboardController::class);
