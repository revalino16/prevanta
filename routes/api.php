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
| Mengikuti pola routes/web.php:
|   - middleware 'auth:sanctum'  → ganti 'auth' (session) untuk mobile token
|   - middleware 'role:xxx'      → cek role user (kader / bidan / orang_tua)
|   - prefix 'xxx'               → organisasi URL per role
|
| Base URL: /api/...
|--------------------------------------------------------------------------
*/

// =========================================================
// [PUBLIC] Health check
// =========================================================
Route::get('/ping', fn () => response()->json(['status' => 'ok', 'app' => 'Prevanta API']));

// =========================================================
// AUTHENTICATION (PUBLIC)
// =========================================================
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

// =========================================================
// SEMUA ROLE YANG SUDAH LOGIN (auth:sanctum)
// =========================================================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
});

// =========================================================
// KADER (role:kader)
// Identik dengan grup Route::middleware(['auth', 'role:kader'])
//   ->prefix('kader') di routes/web.php
// =========================================================
Route::middleware(['auth:sanctum', 'role:kader'])
    ->prefix('kader')
    ->name('kader.')
    ->group(function () {

        // Dashboard kader
        // Identik dengan: GET /kader/dashboard → DashboardController@index
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Monitoring balita (hanya view/read)
        // Identik dengan: GET /kader/monitoring-balita → BalitaController@index
        Route::get('/monitoring-balita', [BalitaController::class, 'index'])->name('monitoring-balita');

        // Daftar orang tua (untuk dropdown form tambah/edit balita)
        Route::get('/orang-tua', [BalitaController::class, 'orangTuaList'])->name('orang-tua');

        // CRUD Balita
        // Identik dengan: tambah, edit, update, destroy balita di web
        Route::apiResource('balita', BalitaController::class)
            ->parameters(['balita' => 'id']);

        // CRUD Jadwal kegiatan posyandu
        // Identik dengan: GET/POST/DELETE /kader/jadwal → JadwalController
        Route::apiResource('jadwal', JadwalController::class)
            ->parameters(['jadwal' => 'id']);

        // Edukasi (kader): read + tambah + hapus
        // Identik dengan: GET /kader/edukasi, POST /tambah-edukasi, DELETE /edukasi/{id}
        // Tidak ada update karena web controller juga tidak punya method update
        Route::get('/edukasi/kategori', [EdukasiController::class, 'kategoriList'])->name('edukasi.kategori');
        Route::apiResource('edukasi', EdukasiController::class)
            ->parameters(['edukasi' => 'id'])
            ->only(['index', 'show', 'store', 'destroy']);
    });

// =========================================================
// BIDAN (role:bidan)
// Identik dengan grup Route::middleware(['auth', 'role:bidan'])
//   ->prefix('bidan') di routes/web.php
// =========================================================
Route::middleware(['auth:sanctum', 'role:bidan'])
    ->prefix('bidan')
    ->name('bidan.')
    ->group(function () {

        // Dashboard bidan
        // Identik dengan: GET /bidan/dashboard → DashboardController@index
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Monitoring balita (bidan juga bisa lihat, sama seperti di web)
        // Identik dengan: GET /bidan/monitoring-balita → BalitaController@index
        Route::get('/monitoring-balita', [BalitaController::class, 'index'])->name('monitoring-balita');
        Route::get('/balita/{id}', [BalitaController::class, 'show'])->name('balita.show');

        // Verifikasi pengukuran (khusus bidan, belum ada Api Controller-nya)
        // Akan ditambahkan saat Api/VerifikasiController dibuat
        // Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        // Route::post('/verifikasi/{pengukuran}', [VerifikasiController::class, 'store'])->name('verifikasi.store');
    });

// =========================================================
// ORANG TUA (role:orang_tua)
// Identik dengan grup Route::middleware(['auth', 'role:orang_tua'])
//   ->prefix('orangtua') di routes/web.php
// =========================================================
Route::middleware(['auth:sanctum', 'role:orang_tua'])
    ->prefix('orangtua')
    ->name('orangtua.')
    ->group(function () {

        // Anakku & riwayat pengukuran
        // Akan ditambahkan saat Api/AnakkuController dibuat
        // Route::get('/anakku', [AnakkuController::class, 'index'])->name('anakku');
        // Route::get('/riwayat', [AnakkuController::class, 'riwayat'])->name('riwayat');
        // Route::get('/balita/{balita}/tindak-lanjut', [AnakkuController::class, 'tindakLanjut']);

        // Edukasi (orang tua): read-only
        // Identik dengan: GET /orangtua/edukasi → OrangTua\EdukasiController@index
        Route::get('/edukasi/kategori', [EdukasiController::class, 'kategoriList'])->name('edukasi.kategori');
        Route::apiResource('edukasi', EdukasiController::class)
            ->parameters(['edukasi' => 'id'])
            ->only(['index', 'show']);
    });
