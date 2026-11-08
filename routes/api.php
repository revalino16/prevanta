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