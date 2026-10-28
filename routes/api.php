<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
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
    // KADER
    // =========================================================
    // Route::prefix('kader')->name('kader.')->group(function () {
    // });
});

// Route::apiResource('dashboard', DashboardController::class);
