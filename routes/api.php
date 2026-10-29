<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Prevanta (Mobile)
|--------------------------------------------------------------------------
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
// AUTHENTICATED (SANCTUM)
// =========================================================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =========================================================
    // ORANG TUA - DASHBOARD (ANAKKU)
    // Identik dengan App\Http\Controllers\OrangTua\AnakkuController@index
    // =========================================================
    Route::get('/ortu/dashboard', function (Request $request) {
        Carbon::setLocale('id');
        $user = $request->user();
        $orangTua = $user?->orangTua;

        // Ambil daftar anak milik orang tua yang login (hanya pengukuran terverifikasi bidan)
        $anakList = $orangTua
            ? $orangTua->balita()
                ->whereHas('pengukuran', function ($query) {
                    $query->whereHas('verifikasi', function ($q) {
                        $q->where('status', 'terverifikasi');
                    });
                })
                ->with([
                    'pengukuran' => function ($query) {
                        $query->whereHas('verifikasi', function ($q) {
                            $q->where('status', 'terverifikasi');
                        })
                            ->with('verifikasi')
                            ->latest('tanggal_pengukuran');
                    },
                ])
                ->get()
                ->map(function ($anak) use ($user) {
                    $p = $anak->pengukuran->first();

                    // Hitung usia bulan persis seperti anakku.blade.php
                    $usiaBulan = $anak->tanggal_lahir
                        ? $anak->tanggal_lahir->diffInMonths(now())
                        : null;
                    $usiaLabel = $usiaBulan !== null
                        ? number_format($usiaBulan, 1, ',', '.') . ' Bulan'
                        : '- Bulan';

                    // Tentukan level warna persis seperti anakku.blade.php (danger, warning, ok)
                    $statusPertumbuhan = $p?->status_pertumbuhan ?? 'Normal';
                    $statusLower = strtolower($statusPertumbuhan);

                    if (
                        str_contains($statusLower, 'sangat pendek') ||
                        str_contains($statusLower, 'sangat kurus') ||
                        str_contains($statusLower, 'buruk')
                    ) {
                        $level = 'danger';
                    } elseif (
                        str_contains($statusLower, 'pendek') ||
                        str_contains($statusLower, 'kurus') ||
                        str_contains($statusLower, 'kurang')
                    ) {
                        $level = 'warning';
                    } else {
                        $level = 'ok';
                    }

                    // Format tindak lanjut & catatan penyuluhan dari verifikasi bidan
                    $verifikasi = $p?->verifikasi;
                    $tindakLanjutRaw = strtolower((string) ($verifikasi?->tindak_lanjut ?? ''));
                    $tindakLanjutLabel = null;
                    if ($tindakLanjutRaw === 'perlu') {
                        $tindakLanjutLabel = 'Perlu Tindak Lanjut & Intervensi';
                    } elseif (!empty($verifikasi?->tindak_lanjut) && $tindakLanjutRaw !== 'tidak_perlu') {
                        $tindakLanjutLabel = $verifikasi->tindak_lanjut;
                    }

                    return [
                        'id' => $anak->id,
                        'nama' => $anak->nama,
                        'nik' => $anak->nik ?? '-',
                        'jenis_kelamin' => strtoupper(substr($anak->jenis_kelamin ?? 'L', 0, 1)),
                        'usia_label' => $usiaLabel,
                        'tanggal_lahir_label' => $anak->tanggal_lahir
                            ? Carbon::parse($anak->tanggal_lahir)->translatedFormat('d M Y')
                            : '-',
                        'nama_orang_tua' => 'Orang Tua: ' . ($user->nama ?? '-'),
                        'alamat' => $anak->alamat,
                        'tanggal_ukur' => $p && $p->tanggal_pengukuran
                            ? Carbon::parse($p->tanggal_pengukuran)->translatedFormat('d M Y')
                            : 'Belum ada pengukuran',
                        'tinggi_badan' => $p?->tinggi_badan ?? 0,
                        'berat_badan' => $p?->berat_badan ?? 0,
                        'lingkar_lengan_atas' => $p?->lingkar_lengan_atas ?? 0,
                        'z_score' => $p?->z_score,
                        'status_pertumbuhan' => $statusPertumbuhan,
                        'level' => $level, // 'danger' | 'warning' | 'ok'
                        'tindak_lanjut_label' => $tindakLanjutLabel,
                        'catatan_penyuluhan' => $verifikasi?->catatan_penyuluhan,
                    ];
                })
                ->values()
            : [];

        // Ambil jadwal terdekat persis seperti AnakkuController.php
        $agendaList = Jadwal::query()
            ->whereDate('tanggal', '>=', Carbon::today())
            ->orderBy('tanggal', 'asc')
            ->get()
            ->map(function ($jadwal) {
                $tanggal = Carbon::parse($jadwal->tanggal);
                $hariLagi = (int) Carbon::today()->diffInDays($tanggal);

                return [
                    'tag' => 'Jadwal',
                    'hari_lagi' => $hariLagi,
                    'judul' => $jadwal->jenis_kegiatan,
                    'deskripsi' => $jadwal->keterangan ?: 'Jadwal kegiatan Posyandu',
                    'tanggal_bulan' => strtoupper($tanggal->translatedFormat('M')),
                    'tanggal_hari' => $tanggal->format('d'),
                    'waktu_label' => $tanggal->translatedFormat('l') . ' Pagi • 08.00 - 11.30 WIB',
                    'lokasi' => $jadwal->lokasi ?: 'Posyandu Jambu 77',
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'posyandu_nama' => 'Posyandu Jambu 77 - Desa Rowo Indah',
                'notif_count' => 0,
                'sapaan_keluarga' => ($user->nama ?? 'Bunda & Ayah') . '!',
                'tanggal_hari_ini' => Carbon::now()->translatedFormat('l, d F Y'),
                'periode_siklus' => 'Siklus ' . Carbon::now()->translatedFormat('M Y'),
                'agenda_list' => $agendaList,
                'anak_list' => $anakList,
            ],
        ]);
    });
});