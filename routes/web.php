<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalitaController;
use App\Http\Controllers\BalitaKmsController;
use App\Http\Controllers\Bidan;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kader;
use App\Http\Controllers\OrangTua;
use App\Http\Controllers\OrangTua\OrangtuaController;
use App\Http\Controllers\OrangTua\EdukasiController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', function () {
    return view('welcome');
});

// =====================================================
// AUTH ROUTES
// =====================================================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

Route::get('/registrasi', [AuthController::class, 'showRegister'])
    ->name('registrasi');

Route::post('/registrasi', [AuthController::class, 'register'])
    ->name('registrasi.post');

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

        Route::get('/anakku', [OrangTua\AnakkuController::class, 'index'])
            ->name('anakku');

        Route::get('/edukasi', [OrangTua\EdukasiController::class, 'index'])
            ->name('edukasi');

        Route::get('/riwayat-pengukuran', [OrangTua\AnakkuController::class, 'riwayat'])
            ->name('riwayat');

        Route::get('/balita/{balita}/kms', BalitaKmsController::class)
            ->name('balita.kms');

        Route::get('/balita/{balita}/tindak-lanjut', [OrangTua\AnakkuController::class, 'tindakLanjut'])
            ->name('balita.tindak-lanjut');
    });



// =====================================================
// BIDAN
// Role database: bidan
// =====================================================

Route::middleware(['auth', 'role:bidan'])
    ->prefix('bidan')
    ->name('bidan.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/monitoring-balita', [BalitaController::class, 'index'])
            ->name('monitoringbalita');

        Route::get('/balita/{balita}/kms', BalitaKmsController::class)
            ->name('balita.kms');

        Route::get('/verifikasi', [Bidan\VerifikasiController::class, 'index'])
            ->name('verifikasi');

        Route::post('/verifikasi/{pengukuran}', [Bidan\VerifikasiController::class, 'store'])
            ->name('verifikasi.store');
    });

// =====================================================
// KADER
// Role database: kader
// =====================================================

Route::middleware(['auth', 'role:kader'])
    ->prefix('kader')
    ->name('kader.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/monitoring-balita', [BalitaController::class, 'index'])
            ->name('monitoringbalita');

        Route::get('/balita/{balita}/kms', BalitaKmsController::class)
            ->name('balita.kms');

        Route::get('/balita/{balita}/pengukuran', [Kader\PengukuranController::class, 'create'])
            ->name('balita.pengukuran.create');

        Route::post('/balita/{balita}/pengukuran', [Kader\PengukuranController::class, 'store'])
            ->name('balita.pengukuran.store');

        Route::get('/tambah-balita', [BalitaController::class, 'create'])
            ->name('balita.tambah');

        Route::post('/tambah-balita', [BalitaController::class, 'store'])
            ->name('balita.store');

        Route::get('/balita/{id}/edit', [BalitaController::class, 'edit'])
            ->name('balita.edit');

        Route::put('/balita/{id}', [BalitaController::class, 'update'])
            ->name('balita.update');

        Route::delete('/balita/{id}', [BalitaController::class, 'destroy'])
            ->name('balita.destroy');

        Route::get('/jadwal', [Kader\JadwalController::class, 'index'])
            ->name('jadwal');

        Route::post('/jadwal', [Kader\JadwalController::class, 'store'])
            ->name('jadwal.store');

        Route::delete('/jadwal/{id}', [Kader\JadwalController::class, 'destroy'])
            ->name('jadwal.destroy');

        Route::get('/edukasi', [Kader\EdukasiController::class, 'index'])
            ->name('edukasi');

        Route::get('/tambah-edukasi', [Kader\EdukasiController::class, 'create'])
            ->name('edukasi.create');

        Route::post('/tambah-edukasi', [Kader\EdukasiController::class, 'store'])
            ->name('edukasi.store');

        Route::delete('/edukasi/{id}', [Kader\EdukasiController::class, 'destroy'])
            ->name('edukasi.destroy');
    });
