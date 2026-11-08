<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BidanController;
use App\Http\Controllers\Api\BalitaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EdukasiController;
use App\Http\Controllers\Api\JadwalController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
//     Route::get('/me', [AuthController::class, 'me'])->name('api.me');
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//     // =========================================================
//     // BIDAN
//     // =========================================================
    // Route::prefix('bidan')->name('api.bidan.')->group(function () {
    //     Route::get('/verifikasi', [BidanController::class, 'verifikasi'])->name('verifikasi');
    //     Route::post('/verifikasi/{pengukuranId}', [BidanController::class, 'storeVerifikasi'])->name('verifikasi.store');
    // });

//     // =========================================================
//     // KADER
//     // BALITA & MONITORING (Mengikuti BalitaController web)
//     // =========================================================
//     Route::get('/monitoring-balita', [BalitaController::class, 'index'])->name('monitoring-balita');
//     Route::get('/orang-tua', [BalitaController::class, 'orangTuaList'])->name('balita.orang-tua');
//     Route::apiResource('balita', BalitaController::class)->parameters(['balita' => 'id']);

//     // =========================================================
//     // JADWAL (Mengikuti JadwalController web)
//     // =========================================================
//     Route::apiResource('jadwal', JadwalController::class)->parameters(['jadwal' => 'id']);

//     // =========================================================
//     // EDUKASI (Mengikuti EdukasiController web)
//     // =========================================================
//     Route::get('/edukasi/kategori', [EdukasiController::class, 'kategoriList'])->name('edukasi.kategori');
//     Route::apiResource('edukasi', EdukasiController::class)->parameters(['edukasi' => 'id']);
// });

// =========================================================
// KADER (role:kader)
// Identik dengan grup Route::middleware(['auth', 'role:kader'])
//   ->prefix('kader') di routes/web.php
// =========================================================

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    Route::get('/me', [AuthController::class, 'me'])
        ->name('api.me');
});

Route::middleware(['auth:sanctum', 'role:kader'])
    ->prefix('kader')
    ->name('api.kader.')
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
    ->name('api.bidan.')
    ->group(function () {

        // Dashboard bidan
        // Identik dengan: GET /bidan/dashboard → DashboardController@index
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Monitoring balita (bidan juga bisa lihat, sama seperti di web)
        // Identik dengan: GET /bidan/monitoring-balita → BalitaController@index
        Route::get('/monitoring-balita', [BalitaController::class, 'index'])->name('monitoring-balita');
        Route::get('/balita/{id}', [BalitaController::class, 'show'])->name('balita.show');
        Route::get('/verifikasi', [BidanController::class, 'verifikasi'])->name('verifikasi');
        Route::post('/verifikasi/{pengukuranId}', [BidanController::class, 'storeVerifikasi'])->name('verifikasi.store');
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
    ->name('api.orangtua.')
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

        // =========================================================
    // ORANG TUA - BUKU KMS & GRAFIK WHO (/api/ortu/monitoring)
    // Identik dengan App\Http\Controllers\BalitaKmsController
    // =========================================================
        Route::get('/ortu/monitoring', function (Request $request) {
            Carbon::setLocale('id');
            $user = $request->user();
            $orangTua = $user?->orangTua;

            $balitaList = $orangTua ? $orangTua->balita()->get() : collect();
            $balitaId = $request->query('balita_id');

            // Pilih balita sesuai balita_id yang dikirim Flutter, atau balita pertama milik orang tua
            $balita = $balitaList->first(fn ($b) => (string) $b->id === (string) $balitaId)
                ?? $balitaList->first();

            if (! $balita) {
                return response()->json(['success' => false, 'message' => 'Data balita tidak ditemukan'], 404);
            }

            $balita->load([
                'pengukuran' => fn ($q) => $q->whereHas('verifikasi', fn ($v) => $v->where('status', 'terverifikasi'))
                    ->oldest('tanggal_pengukuran'),
                'pengukuran.kader',
                'pengukuran.verifikasi.bidan',
                'imunisasi.jenisImunisasi',
                'imunisasi.kader',
                'vitamin.jenisVitamin',
                'vitamin.kader',
            ]);

            $birthDate = Carbon::parse($balita->tanggal_lahir);
            $diffNow = $birthDate->diff(now());
            $usiaSekarangLabel = ($diffNow->y > 0 ? $diffNow->y . ' thn ' : '') . $diffNow->m . ' bln';

            // Pengukuran terakhir yang sudah terverifikasi bidan
            $last = $balita->pengukuran->last();
            $pengukuranTerakhir = null;

            if ($last) {
                $tglUkur = Carbon::parse($last->tanggal_pengukuran);
                $usiaUkurBulan = (int) $birthDate->diffInMonths($tglUkur);
                $status = $last->status_pertumbuhan ?? 'Normal';
                $statusLower = strtolower($status);
                $tone = (str_contains($statusLower, 'sangat pendek') || str_contains($statusLower, 'buruk'))
                    ? 'danger'
                    : ((str_contains($statusLower, 'pendek') || str_contains($statusLower, 'kurang')) ? 'warning' : 'ok');

                $verif = $last->verifikasi;
                $lilaVal = (float) ($last->lingkar_lengan_atas ?? 0);
                $lilaLabel = $lilaVal <= 0 ? 'Belum dicatat' : ($lilaVal < 11.5 ? 'Pita Merah - Gizi Buruk' : ($lilaVal < 12.5 ? 'Pita Kuning - Waspada' : 'Pita Hijau - Normal'));

                $pengukuranTerakhir = [
                    'id' => $last->id,
                    'tanggal' => $tglUkur->translatedFormat('d F Y'),
                    'usia_label' => 'Usia ' . $usiaUkurBulan . ' Bulan',
                    'is_verified' => $verif?->status === 'terverifikasi',
                    'verifikasi_label' => $verif?->bidan?->nama
                        ? 'Terverifikasi oleh ' . $verif->bidan->nama
                        : 'Terverifikasi oleh Bidan Desa',
                    'tinggi_badan' => (float) ($last->tinggi_badan ?? 0),
                    'berat_badan' => (float) ($last->berat_badan ?? 0),
                    'lila' => $lilaVal,
                    'lingkar_kepala' => (float) ($last->lingkar_kepala ?? 0),
                    'z_score' => $last->z_score !== null ? (float) $last->z_score : null,
                    'status_pertumbuhan' => $status,
                    'tone' => $tone,
                    'bb_badge' => $last->berat_badan ? 'BB Tercatat • Sesuai' : 'Belum dicatat',
                    'lila_badge' => $lilaLabel,
                    'lk_badge' => $last->lingkar_kepala ? 'Normosefali - Sesuai' : 'Belum dicatat',
                    'tindak_lanjut' => $tone === 'ok'
                        ? 'Pertumbuhan anak baik. Lanjutkan pemantauan rutin di Posyandu.'
                        : ($verif?->tindak_lanjut === 'perlu'
                            ? 'Perlu rujukan segera ke Puskesmas / tindak lanjut arahan Bidan Desa.'
                            : 'Perlu pemantauan berkala pada jadwal Posyandu berikutnya.'),
                    'catatan_penyuluhan' => $verif?->catatan_penyuluhan,
                    'konselor' => $verif?->bidan?->nama ?? 'Bidan Desa',
                    'pendamping' => $last->kader?->nama ?? 'Kader Posyandu',
                ];
            }

            // Gabungan Riwayat Imunisasi & Vitamin
            $imunisasiList = $balita->imunisasi->map(fn ($i) => [
                'id' => $i->id,
                'tipe' => 'Imunisasi',
                'nama' => $i->jenisImunisasi?->nama_imunisasi ?? 'Imunisasi',
                'deskripsi' => $i->jenisImunisasi?->deskripsi ?? 'Imunisasi Dasar Balita',
                'tanggal' => Carbon::parse($i->tanggal_pemberian)->translatedFormat('d F Y'),
                'usia_label' => 'Usia ' . (int) $birthDate->diffInMonths(Carbon::parse($i->tanggal_pemberian)) . ' Bulan',
                'status' => ucfirst((string) ($i->status ?? 'Tercatat')),
                'petugas' => $i->kader?->nama ?? 'Kader Posyandu',
            ]);

            $vitaminList = $balita->vitamin->map(fn ($v) => [
                'id' => $v->id,
                'tipe' => 'Vitamin',
                'nama' => $v->jenisVitamin?->nama_vitamin ?? 'Vitamin A',
                'deskripsi' => $v->jenisVitamin?->deskripsi ?? 'Suplementasi Vitamin Balita',
                'tanggal' => Carbon::parse($v->tanggal_pemberian)->translatedFormat('d F Y'),
                'usia_label' => 'Usia ' . (int) $birthDate->diffInMonths(Carbon::parse($v->tanggal_pemberian)) . ' Bulan',
                'status' => ucfirst((string) ($v->status ?? 'Tercatat')),
                'petugas' => $v->kader?->nama ?? 'Kader Posyandu',
            ]);

            // Titik-titik Grafik KMS (TB/U, BB/U, BB/TB)
            $tbUPoints = $balita->pengukuran->filter(fn ($m) => $m->z_score !== null)->map(function ($m) use ($birthDate) {
                $age = max(0, (int) $birthDate->diffInMonths(Carbon::parse($m->tanggal_pengukuran)));
                return [
                    'x' => (float) $age,
                    'y' => (float) $m->z_score,
                    'label_x' => $age === 0 ? 'Lahir' : $age . ' Bln',
                    'callout_title' => 'TB ' . number_format((float) $m->tinggi_badan, 1) . ' cm (' . number_format((float) $m->z_score, 2) . ')',
                    'callout_sub' => $m->status_pertumbuhan ?? 'Normal',
                ];
            })->values();

            $bbUPoints = $balita->pengukuran->filter(fn ($m) => $m->berat_badan !== null)->map(function ($m) use ($birthDate) {
                $age = max(0, (int) $birthDate->diffInMonths(Carbon::parse($m->tanggal_pengukuran)));
                return [
                    'x' => (float) $age,
                    'y' => (float) $m->berat_badan,
                    'label_x' => $age === 0 ? 'Lahir' : $age . ' Bln',
                    'callout_title' => 'BB ' . number_format((float) $m->berat_badan, 2) . ' kg',
                    'callout_sub' => 'Usia ' . $age . ' Bulan',
                ];
            })->values();

            $bbTbPoints = $balita->pengukuran->filter(fn ($m) => $m->berat_badan !== null && $m->tinggi_badan !== null)
                ->sortBy('tinggi_badan')
                ->map(function ($m) {
                    return [
                        'x' => (float) $m->tinggi_badan,
                        'y' => (float) $m->berat_badan,
                        'label_x' => number_format((float) $m->tinggi_badan, 0) . ' cm',
                        'callout_title' => number_format((float) $m->berat_badan, 2) . ' kg / ' . number_format((float) $m->tinggi_badan, 1) . ' cm',
                        'callout_sub' => $m->status_pertumbuhan ?? 'Proporsi',
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $balita->id,
                    'kode_balita' => 'BLT-' . str_pad((string) $balita->id, 3, '0', STR_PAD_LEFT),
                    'nama' => $balita->nama,
                    'nik' => $balita->nik ?? '-',
                    'jenis_kelamin' => strtoupper(substr($balita->jenis_kelamin ?? 'L', 0, 1)),
                    'tanggal_lahir_label' => $birthDate->translatedFormat('d F Y'),
                    'usia_sekarang_label' => $usiaSekarangLabel,
                    'nama_orang_tua' => $user->nama ?? 'Bunda & Ayah',
                    'domisili' => $balita->alamat ?: 'Belum tersedia',
                    'posyandu_nama' => 'Posyandu Jambu 77 - Desa Rowo Indah',
                    'notif_count' => 3,
                    'pengukuran_terakhir' => $pengukuranTerakhir,
                    'imunisasi_vitamin' => $imunisasiList->concat($vitaminList)->values(),
                    'charts' => [
                        'tb-u' => $tbUPoints,
                        'bb-u' => $bbUPoints,
                        'bb-tb' => $bbTbPoints,
                    ],
                ],
            ]);
        });
    });
