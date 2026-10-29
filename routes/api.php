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
| Setiap controller di folder App\Http\Controllers\Api merefleksikan
| controller dan fungsi halaman yang ada di web (Single Responsibility).
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
// Tambahkan di dalam Route::middleware(['auth:sanctum'])->group(function () { ... })
Route::get('/ortu/dashboard', function (Request $request) {
    Carbon::setLocale('id');
    $user = $request->user();
    $orangTua = $user->orangTua;

    $anakList = $orangTua
        ? $orangTua->balita()
            ->with(['pengukuran' => function ($q) {
                $q->whereHas('verifikasi', fn ($v) => $v->where('status', 'terverifikasi'))
                    ->with('verifikasi')
                    ->latest('tanggal_pengukuran');
            }])
            ->get()
            ->map(function ($b) use ($user) {
                $p = $b->pengukuran->first();
                $usiaBulan = $b->tanggal_lahir
                    ? (int) Carbon::parse($b->tanggal_lahir)->diffInMonths(now())
                    : 0;

                return [
                    'id' => $b->id,
                    'nama' => $b->nama,
                    'nik' => $b->nik,
                    'jenis_kelamin' => $b->jenis_kelamin,
                    'usia_label' => $usiaBulan.' Bulan',
                    'tanggal_lahir_label' => $b->tanggal_lahir
                        ? Carbon::parse($b->tanggal_lahir)->translatedFormat('d M Y')
                        : '-',
                    'info_ortu_alamat' => 'Ibu: '.($user->nama ?? '-').($b->alamat ? ' • '.$b->alamat : ''),
                    'tanggal_ukur' => $p && $p->tanggal_pengukuran
                        ? Carbon::parse($p->tanggal_pengukuran)->translatedFormat('d M Y')
                        : 'Belum diukur',
                    'tinggi_badan' => $p->tinggi_badan ?? 0,
                    'berat_badan' => $p->berat_badan ?? 0,
                    'lingkar_lengan_atas' => $p->lingkar_lengan_atas ?? 0,
                    'z_score' => $p->z_score ?? 0,
                    'status_pertumbuhan' => $p->status_pertumbuhan ?? 'Normal',
                    'tindak_lanjut' => $p?->verifikasi?->tindak_lanjut,
                    'catatan_penyuluhan' => $p?->verifikasi?->catatan_penyuluhan,
                ];
            })
        : [];

    $agendaList = Jadwal::whereDate('tanggal', '>=', today())
        ->orderBy('tanggal', 'asc')
        ->get()
        ->map(function ($j) {
            $tgl = Carbon::parse($j->tanggal);

            return [
                'tag' => 'PRIORITAS UTAMA',
                'hari_lagi' => (int) today()->diffInDays($tgl),
                'judul' => $j->jenis_kegiatan,
                'deskripsi' => $j->keterangan ?: 'Jadwal kegiatan Posyandu rutin.',
                'tanggal_bulan' => strtoupper($tgl->translatedFormat('M')),
                'tanggal_hari' => $tgl->format('d'),
                'waktu_label' => $tgl->translatedFormat('l').' Pagi',
                'jam_mulai' => '08.30',
                'lokasi' => $j->lokasi ?: 'Posyandu',
            ];
        });

    return response()->json([
        'success' => true,
        'data' => [
            'posyandu_nama' => 'Posyandu Jambu 77 - Desa Rowo Indah',
            'notif_count' => 3,
            'sapaan_keluarga' => $user->nama ?? 'Bunda & Ayah!',
            'tanggal_hari_ini' => now()->translatedFormat('l, d F Y'),
            'periode_siklus' => 'Siklus '.now()->translatedFormat('M Y'),
            'agenda_list' => $agendaList,
            'anak_list' => $anakList,
        ],
    ]);
});
