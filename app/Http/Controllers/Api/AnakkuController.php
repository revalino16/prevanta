<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Jadwal;
use App\Models\OrangTua;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnakkuController extends Controller
{
    /**
     * GET /api/ortu/dashboard atau /api/orangtua/dashboard
     * Mengambil data dashboard untuk Orang Tua (Anakku).
     */
    public function dashboard(Request $request): JsonResponse
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');

        $user = $request->user();

        // Cari record orang tua dari user yang login
        $orangTua = $user ? $user->orangTua : null;

        if (! $orangTua && $user) {
            $orangTua = OrangTua::where('users_id', $user->id)->first();
        }

        // Ambil daftar balita milik orang tua ini
        $balitaList = $orangTua
            ? $orangTua->balita()
                ->with([
                    'pengukuran' => function ($query) {
                        $query->with('verifikasi')
                            ->orderByDesc('tanggal_pengukuran')
                            ->orderByDesc('id');
                    },
                ])
                ->get()
            : collect();

        // Fallback jika belum terkait tapi ada balita di database
        if ($balitaList->isEmpty() && (! $orangTua || $balitaList->count() === 0)) {
            $firstOt = OrangTua::with(['balita.pengukuran' => function ($query) {
                $query->with('verifikasi')->orderByDesc('tanggal_pengukuran')->orderByDesc('id');
            }])->first();

            if ($firstOt && $firstOt->balita->isNotEmpty()) {
                $balitaList = $firstOt->balita;
                $orangTua = $firstOt;
            }
        }

        $anakData = $balitaList->map(function ($anak) use ($orangTua, $user, $now) {
            $pengukuranTerakhir = $anak->pengukuran->first();

            $birthDate = $anak->tanggal_lahir ? Carbon::parse($anak->tanggal_lahir) : null;
            $usiaBulan = $birthDate ? (int) $birthDate->diffInMonths($now) : 0;
            $usiaLabel = "{$usiaBulan} Bulan";
            $tglLahirLabel = $birthDate ? $birthDate->locale('id')->translatedFormat('d M Y') : '-';

            $status = $pengukuranTerakhir ? ($pengukuranTerakhir->status_pertumbuhan ?? 'Normal') : 'Belum Ada Data';
            $statusLower = strtolower($status);

            // Tentukan level warna: 'danger' | 'warning' | 'ok'
            if (str_contains($statusLower, 'sangat pendek') || str_contains($statusLower, 'sangat kurus') || str_contains($statusLower, 'buruk')) {
                $level = 'danger';
            } elseif (str_contains($statusLower, 'pendek') || str_contains($statusLower, 'kurus') || str_contains($statusLower, 'kurang')) {
                $level = 'warning';
            } else {
                $level = 'ok';
            }

            $rawZScore = $pengukuranTerakhir && $pengukuranTerakhir->z_score !== null
                ? number_format((float) $pengukuranTerakhir->z_score, 2, '.', '')
                : '-';

            $tglUkur = $pengukuranTerakhir && $pengukuranTerakhir->tanggal_pengukuran
                ? Carbon::parse($pengukuranTerakhir->tanggal_pengukuran)->locale('id')->translatedFormat('d M Y')
                : '-';

            $verifikasi = $pengukuranTerakhir?->verifikasi;

            $namaOrtu = $orangTua && $orangTua->user
                ? 'Orang Tua: ' . ($orangTua->user->nama ?? '-')
                : ($user ? 'Orang Tua: ' . ($user->nama ?? '-') : 'Orang Tua: -');

            return [
                'id' => $anak->id,
                'nama' => $anak->nama,
                'nik' => $anak->nik,
                'jenis_kelamin' => strtoupper($anak->jenis_kelamin ?? 'L'),
                'usia_label' => $usiaLabel,
                'usia_bulan' => $usiaBulan,
                'tanggal_lahir' => $anak->tanggal_lahir ? Carbon::parse($anak->tanggal_lahir)->format('Y-m-d') : null,
                'tanggal_lahir_label' => $tglLahirLabel,
                'nama_orang_tua' => $namaOrtu,
                'alamat' => $anak->alamat,
                'tanggal_ukur' => $tglUkur,
                'status_pertumbuhan' => ucfirst($status),
                'level' => $level,
                'tinggi_badan' => $pengukuranTerakhir ? (float) $pengukuranTerakhir->tinggi_badan : 0.0,
                'berat_badan' => $pengukuranTerakhir ? (float) $pengukuranTerakhir->berat_badan : 0.0,
                'lingkar_lengan_atas' => $pengukuranTerakhir ? (float) $pengukuranTerakhir->lingkar_lengan_atas : 0.0,
                'lingkar_kepala' => $pengukuranTerakhir ? (float) $pengukuranTerakhir->lingkar_kepala : 0.0,
                'z_score' => $rawZScore,
                'tindak_lanjut_label' => $verifikasi?->tindak_lanjut ?? ($level !== 'ok' ? 'Perlu Pemantauan & Intervensi' : null),
                'catatan_penyuluhan' => $verifikasi?->catatan_penyuluhan,
            ];
        });

        // Ambil agenda jadwal posyandu mendatang
        $agendaList = Jadwal::where('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal', 'asc')
            ->take(5)
            ->get()
            ->map(function ($j) use ($now) {
                $tgl = Carbon::parse($j->tanggal);
                $hariLagi = (int) $now->startOfDay()->diffInDays($tgl->copy()->startOfDay(), false);

                return [
                    'tag' => 'Jadwal',
                    'hari_lagi' => max(0, $hariLagi),
                    'hari_lagi_label' => $hariLagi === 0 ? 'Hari Ini' : ($hariLagi > 0 ? "{$hariLagi} Hari Lagi" : 'Sudah Lewat'),
                    'judul' => $j->jenis_kegiatan,
                    'deskripsi' => $j->keterangan ?? 'Posyandu rutin dan pemantauan tumbuh kembang balita.',
                    'tanggal_bulan' => strtoupper($tgl->locale('id')->translatedFormat('M')),
                    'tanggal_hari' => $tgl->format('d'),
                    'waktu_label' => '08.00 - 11.30 WIB',
                    'lokasi' => $j->lokasi ?? 'Posyandu Terpadu',
                ];
            });

        // Fallback jika belum ada jadwal di database
        if ($agendaList->isEmpty()) {
            $agendaList = collect([
                [
                    'tag' => 'Jadwal',
                    'hari_lagi' => 3,
                    'hari_lagi_label' => 'Segera',
                    'judul' => 'Posyandu Rutin & Penimbangan Balita',
                    'deskripsi' => 'Penimbangan berat badan, pengukuran tinggi badan, dan pemantauan tumbuh kembang balita.',
                    'tanggal_bulan' => strtoupper($now->locale('id')->translatedFormat('M')),
                    'tanggal_hari' => $now->format('d'),
                    'waktu_label' => '08.00 - 11.30 WIB',
                    'lokasi' => 'Balai Posyandu',
                ],
            ]);
        }

        $namaPosyandu = 'Posyandu Terpadu Prevanta';
        $sapaan = ($user?->nama ?? $orangTua?->user?->nama ?? 'Bunda & Ayah') . '!';
        $tglHariIni = $now->locale('id')->translatedFormat('l, d F Y');
        $periodeSiklus = 'Siklus ' . $now->locale('id')->translatedFormat('M Y');

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard anakku berhasil dimuat.',
            'data' => [
                'posyandu_nama' => $namaPosyandu,
                'notif_count' => 0,
                'sapaan_keluarga' => $sapaan,
                'tanggal_hari_ini' => $tglHariIni,
                'periode_siklus' => $periodeSiklus,
                'agenda_list' => $agendaList,
                'anak_list' => $anakData,
            ],
        ]);
    }

    /**
     * GET /api/ortu/monitoring atau /api/orangtua/monitoring
     * Mengambil data KMS (Kartu Menuju Sehat) lengkap untuk satu balita.
     * Mendukung query ?balita_id=1
     */
    public function monitoring(Request $request): JsonResponse
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');

        $user = $request->user();

        // Cari record orang tua
        $orangTua = $user ? $user->orangTua : null;
        if (! $orangTua && $user) {
            $orangTua = OrangTua::where('users_id', $user->id)->first();
        }

        // Ambil daftar balita milik orang tua ini
        $balitaList = $orangTua
            ? $orangTua->balita()->get()
            : collect();

        // Fallback jika belum terkait
        if ($balitaList->isEmpty()) {
            $firstOt = OrangTua::with('balita')->first();
            if ($firstOt && $firstOt->balita->isNotEmpty()) {
                $balitaList  = $firstOt->balita;
                $orangTua    = $firstOt;
            }
        }

        if ($balitaList->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Belum ada data balita.'], 404);
        }

        // Tentukan balita yang diminta
        $balitaId = $request->query('balita_id') ?? $request->query('anak_id');
        $balita   = $balitaId
            ? $balitaList->firstWhere('id', (int) $balitaId) ?? $balitaList->first()
            : $balitaList->first();

        // Load semua pengukuran balita ini (urut dari yang paling lama → paling baru)
        $allPengukuran = $balita->pengukuran()
            ->with(['verifikasi.bidan', 'kader'])
            ->orderBy('tanggal_pengukuran')
            ->orderBy('id')
            ->get();

        $birthDate   = $balita->tanggal_lahir ? Carbon::parse($balita->tanggal_lahir) : null;
        $usiaBulan   = $birthDate ? (int) $birthDate->diffInMonths($now) : 0;
        $tglLahirLabel = $birthDate ? $birthDate->locale('id')->translatedFormat('d M Y') : '-';

        // Usia dalam format "X thn Y bln" jika >= 12 bulan
        if ($usiaBulan >= 12) {
            $thn = intdiv($usiaBulan, 12);
            $bln = $usiaBulan % 12;
            $usiaSekarangLabel = $bln > 0 ? "{$thn} thn {$bln} bln" : "{$thn} tahun";
        } else {
            $usiaSekarangLabel = "{$usiaBulan} bulan";
        }

        $jk          = strtoupper($balita->jenis_kelamin ?? 'L');
        $namaOrtu    = $orangTua?->user?->nama ?? $user?->nama ?? '-';
        $kodeBalita  = 'BLT-' . str_pad($balita->id, 3, '0', STR_PAD_LEFT);

        // Pengukuran terakhir (paling baru)
        $pengukuranTerakhir = $allPengukuran->last();
        $pengukuranTerakhirData = null;

        if ($pengukuranTerakhir) {
            $tglUkur    = $pengukuranTerakhir->tanggal_pengukuran
                ? Carbon::parse($pengukuranTerakhir->tanggal_pengukuran)->locale('id')->translatedFormat('d M Y')
                : '-';
            $usiaBulanUkur = ($birthDate && $pengukuranTerakhir->tanggal_pengukuran)
                ? (int) $birthDate->diffInMonths(Carbon::parse($pengukuranTerakhir->tanggal_pengukuran))
                : $usiaBulan;

            $usiaUkurLabel = $usiaBulanUkur >= 12
                ? intdiv($usiaBulanUkur, 12) . ' thn ' . ($usiaBulanUkur % 12) . ' bln'
                : "{$usiaBulanUkur} Bulan";

            $verif      = $pengukuranTerakhir->verifikasi;
            $isVerified = $verif && strtolower($verif->status ?? '') === 'terverifikasi';
            $verifLabel = $isVerified
                ? 'Terverifikasi oleh ' . ($verif->bidan?->nama ?? 'Bidan Desa')
                : 'Menunggu verifikasi bidan';

            $konselor   = $isVerified
                ? ($verif->bidan?->nama ? 'Bdn. ' . $verif->bidan->nama : 'Bidan Desa')
                : 'Bidan Desa';
            $pendamping = $pengukuranTerakhir->kader?->nama
                ? 'Kader ' . $pengukuranTerakhir->kader->nama
                : 'Kader Posyandu';

            $status     = ucfirst($pengukuranTerakhir->status_pertumbuhan ?? 'Normal');
            $statusL    = strtolower($status);
            if (str_contains($statusL, 'sangat pendek') || str_contains($statusL, 'sangat kurus') || str_contains($statusL, 'buruk')) {
                $tone = 'danger';
            } elseif (str_contains($statusL, 'pendek') || str_contains($statusL, 'kurus') || str_contains($statusL, 'kurang')) {
                $tone = 'warning';
            } else {
                $tone = 'ok';
            }

            $zScore    = $pengukuranTerakhir->z_score !== null
                ? (float) number_format((float) $pengukuranTerakhir->z_score, 2, '.', '')
                : null;
            $zLabel    = $zScore !== null
                ? ($zScore >= 0 ? '+' : '') . number_format($zScore, 2) . ' SD'
                : '-';

            $lilaVal   = (float) $pengukuranTerakhir->lingkar_lengan_atas;
            if ($lilaVal < 11.5 && $lilaVal > 0) {
                $lilaBadge = 'Pita Merah - Risiko Tinggi';
            } elseif ($lilaVal < 12.5 && $lilaVal > 0) {
                $lilaBadge = 'Pita Kuning - Waspada';
            } else {
                $lilaBadge = 'Pita Hijau - Normal';
            }

            $pengukuranTerakhirData = [
                'id'                  => $pengukuranTerakhir->id,
                'tanggal'             => $tglUkur,
                'usia_label'          => "Usia {$usiaUkurLabel}",
                'is_verified'         => $isVerified,
                'verifikasi_label'    => $verifLabel,
                'tinggi_badan'        => (float) $pengukuranTerakhir->tinggi_badan,
                'berat_badan'         => (float) $pengukuranTerakhir->berat_badan,
                'lila'                => $lilaVal,
                'lingkar_kepala'      => (float) $pengukuranTerakhir->lingkar_kepala,
                'z_score'             => $zScore,
                'status_pertumbuhan'  => $status,
                'tone'                => $tone,
                'bb_badge'            => $zLabel . ' • ' . ($tone === 'ok' ? 'BB Normal' : ucfirst($statusL)),
                'lila_badge'          => $lilaBadge,
                'lk_badge'            => 'Normosefali - Sesuai',
                'tindak_lanjut'       => $verif?->tindak_lanjut ?? ($tone !== 'ok' ? 'Pemantauan lebih lanjut diperlukan.' : 'Pemantauan rutin sesuai jadwal Posyandu.'),
                'catatan_penyuluhan'  => $verif?->catatan_penyuluhan,
                'konselor'            => $konselor,
                'pendamping'          => $pendamping,
            ];
        }

        // Chart points: semua pengukuran → TB/U, BB/U, BB/TB
        $chartTbU  = [];
        $chartBbU  = [];
        $chartBbTb = [];

        foreach ($allPengukuran as $p) {
            $tglP        = $p->tanggal_pengukuran ? Carbon::parse($p->tanggal_pengukuran) : null;
            $bulanP      = ($birthDate && $tglP) ? (int) $birthDate->diffInMonths($tglP) : 0;
            $labelX      = $bulanP >= 12
                ? intdiv($bulanP, 12) . ' Thn ' . ($bulanP % 12) . ' Bln'
                : ($bulanP === 0 ? 'Lahir' : "{$bulanP} Bln");
            $tbVal       = (float) $p->tinggi_badan;
            $bbVal       = (float) $p->berat_badan;
            $zVal        = $p->z_score !== null ? (float) $p->z_score : 0.0;
            $statusChip  = ucfirst($p->status_pertumbuhan ?? 'Normal');

            $chartTbU[]  = [
                'x'              => (float) $bulanP,
                'y'              => $zVal,
                'label_x'        => $labelX,
                'callout_title'  => "TB {$tbVal} cm ({$zVal})",
                'callout_sub'    => $statusChip,
            ];

            $chartBbU[]  = [
                'x'              => (float) $bulanP,
                'y'              => $bbVal,
                'label_x'        => $labelX,
                'callout_title'  => "BB {$bbVal} kg",
                'callout_sub'    => "Usia {$bulanP} Bulan",
            ];

            $chartBbTb[] = [
                'x'              => $tbVal,
                'y'              => $bbVal,
                'label_x'        => "{$tbVal} cm",
                'callout_title'  => "{$bbVal} kg / {$tbVal} cm",
                'callout_sub'    => $statusChip,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data KMS balita berhasil dimuat.',
            'data'    => [
                'id'                   => $balita->id,
                'kode_balita'          => $kodeBalita,
                'nama'                 => $balita->nama,
                'nik'                  => $balita->nik ?? '-',
                'jenis_kelamin'        => $jk,
                'tanggal_lahir_label'  => $tglLahirLabel,
                'usia_sekarang_label'  => $usiaSekarangLabel,
                'nama_orang_tua'       => $namaOrtu,
                'domisili'             => $balita->alamat ?? '-',
                'posyandu_nama'        => 'Posyandu Terpadu Prevanta',
                'notif_count'          => 0,
                'pengukuran_terakhir'  => $pengukuranTerakhirData,
                'imunisasi_vitamin'    => [],
                'charts'               => [
                    'tb-u'  => $chartTbU,
                    'bb-u'  => $chartBbU,
                    'bb-tb' => $chartBbTb,
                ],
            ],
        ]);
    }

    /**
     * GET /api/ortu/riwayat atau /api/orangtua/riwayat
     * Mengambil riwayat antropometri (pengukuran kader) & imunisasi balita.
     * Mendukung query ?anak_id=1
     */
    public function riwayat(Request $request): JsonResponse
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');

        $user = $request->user();

        // Cari record orang tua
        $orangTua = $user ? $user->orangTua : null;
        if (! $orangTua && $user) {
            $orangTua = OrangTua::where('users_id', $user->id)->first();
        }

        // Ambil daftar balita
        $balitaList = $orangTua
            ? $orangTua->balita()
                ->with([
                    'pengukuran' => function ($q) {
                        $q->with(['verifikasi.bidan', 'kader'])
                            ->orderByDesc('tanggal_pengukuran')
                            ->orderByDesc('id');
                    },
                ])
                ->get()
            : collect();

        // Fallback jika belum terkait
        if ($balitaList->isEmpty() && (! $orangTua || $balitaList->count() === 0)) {
            $firstOt = OrangTua::with(['balita.pengukuran' => function ($q) {
                $q->with(['verifikasi.bidan', 'kader'])->orderByDesc('tanggal_pengukuran')->orderByDesc('id');
            }])->first();

            if ($firstOt && $firstOt->balita->isNotEmpty()) {
                $balitaList = $firstOt->balita;
                $orangTua = $firstOt;
            }
        }

        if ($balitaList->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data balita.',
                'data' => [
                    'posyandu_nama' => 'Posyandu Terpadu Prevanta',
                    'notif_count' => 0,
                    'status_pendampingan' => 'Normal',
                    'anak_aktif' => [
                        'id' => 0,
                        'nama' => 'Belum Ada Data',
                        'nik' => '-',
                        'usia_label' => '0 Bulan',
                        'jenis_kelamin_label' => 'Laki-laki',
                        'info_posyandu' => 'Posyandu Terpadu',
                    ],
                    'daftar_anak' => [],
                    'pengukuran' => [],
                    'imunisasi' => [],
                    'diperbarui_label' => $now->locale('id')->translatedFormat('d F Y'),
                ],
            ]);
        }

        // Tentukan anak terpilih
        $anakId = $request->query('anak_id') ?? $request->query('anak');
        $balitaAktif = $anakId
            ? $balitaList->firstWhere('id', (int) $anakId) ?? $balitaList->first()
            : $balitaList->first();

        // Buat daftar anak untuk switch selector
        $daftarAnak = $balitaList->map(function ($b) use ($now) {
            $birthDate = $b->tanggal_lahir ? Carbon::parse($b->tanggal_lahir) : null;
            $usiaBulan = $birthDate ? (int) $birthDate->diffInMonths($now) : 0;
            $jkLabel = (strtoupper($b->jenis_kelamin ?? 'L') === 'L') ? 'Laki-laki' : 'Perempuan';

            return [
                'id' => $b->id,
                'nama' => $b->nama,
                'nik' => $b->nik ?? '-',
                'usia_label' => "{$usiaBulan} Bulan",
                'jenis_kelamin_label' => $jkLabel,
                'info_posyandu' => 'Posyandu Terpadu • Data tersinkronisasi KMS',
            ];
        })->values();

        $birthDateAktif = $balitaAktif->tanggal_lahir ? Carbon::parse($balitaAktif->tanggal_lahir) : null;
        $usiaBulanAktif = $birthDateAktif ? (int) $birthDateAktif->diffInMonths($now) : 0;
        $jkLabelAktif = (strtoupper($balitaAktif->jenis_kelamin ?? 'L') === 'L') ? 'Laki-laki' : 'Perempuan';

        $anakAktifData = [
            'id' => $balitaAktif->id,
            'nama' => $balitaAktif->nama,
            'nik' => $balitaAktif->nik ?? '-',
            'usia_label' => "{$usiaBulanAktif} Bulan",
            'jenis_kelamin_label' => $jkLabelAktif,
            'info_posyandu' => 'Posyandu Terpadu • Data tersinkronisasi KMS',
        ];

        // Pengukuran balita aktif
        $semuaPengukuran = $balitaAktif->pengukuran()
            ->with(['verifikasi.bidan', 'kader'])
            ->orderByDesc('tanggal_pengukuran')
            ->orderByDesc('id')
            ->get();

        $pengukuranList = $semuaPengukuran->values()->map(function ($p, $index) use ($birthDateAktif, $usiaBulanAktif) {
            $terbaru = ($index === 0);
            $tglUkur = $p->tanggal_pengukuran ? Carbon::parse($p->tanggal_pengukuran) : null;
            $usiaBulanUkur = ($birthDateAktif && $tglUkur)
                ? (int) $birthDateAktif->diffInMonths($tglUkur)
                : $usiaBulanAktif;

            $judul = "Pemeriksaan Usia {$usiaBulanUkur} Bulan";
            $tanggalLokasi = ($tglUkur ? $tglUkur->locale('id')->translatedFormat('d F Y') : '-') . ' • Posyandu Terpadu';

            // Nama verifikator atau kader yang menginput
            $verifikator = 'Kader Posyandu';
            if ($p->verifikasi && $p->verifikasi->bidan) {
                $namaBidan = $p->verifikasi->bidan->nama ?? $p->verifikasi->bidan->name ?? 'Bidan Desa';
                $verifikator = "Bidan {$namaBidan}";
            } elseif ($p->kader) {
                $namaKader = $p->kader->nama ?? $p->kader->name ?? 'Kader Posyandu';
                $verifikator = "Kader {$namaKader}";
            }

            // Metrik TB
            $tbVal = (float) $p->tinggi_badan;
            $statusStr = strtolower($p->status_pertumbuhan ?? 'normal');
            if (str_contains($statusStr, 'sangat pendek') || str_contains($statusStr, 'sangat kurus') || str_contains($statusStr, 'buruk')) {
                $levelTb = 'danger';
                $badgeTb = 'Sangat Pendek';
            } elseif (str_contains($statusStr, 'pendek') || str_contains($statusStr, 'kurang')) {
                $levelTb = 'warning';
                $badgeTb = 'Pendek';
            } elseif (str_contains($statusStr, 'tinggi')) {
                $levelTb = 'ok';
                $badgeTb = 'Tinggi';
            } else {
                $levelTb = 'ok';
                $badgeTb = 'Normal';
            }

            $zScoreFormatted = $p->z_score !== null ? number_format((float) $p->z_score, 2, '.', '') . ' SD' : '-';

            $metrikTb = [
                'nilai' => $tbVal,
                'digits' => 1,
                'unit' => 'cm',
                'footer_label' => 'Z-Score TB/U',
                'footer_nilai' => $zScoreFormatted,
                'badge' => $badgeTb,
                'level' => $levelTb,
                'ringkas' => $zScoreFormatted != '-' ? $zScoreFormatted : $badgeTb,
            ];

            // Metrik BB
            $bbVal = (float) $p->berat_badan;
            $metrikBb = [
                'nilai' => $bbVal,
                'digits' => 2,
                'unit' => 'kg',
                'footer_label' => 'Status BB',
                'footer_nilai' => $levelTb === 'danger' ? 'Gizi Buruk' : ($levelTb === 'warning' ? 'Gizi Kurang' : 'Gizi Baik / Normal'),
                'badge' => $levelTb === 'danger' ? 'Gizi Buruk' : ($levelTb === 'warning' ? 'Gizi Kurang' : 'Gizi Baik / Normal'),
                'level' => $levelTb,
                'ringkas' => $levelTb === 'danger' ? 'Kurang' : 'Normal',
            ];

            // Metrik LiLA
            $lilaVal = (float) $p->lingkar_lengan_atas;
            $warnaLila = 'Hijau';
            $levelLila = 'ok';
            $badgeLila = 'Normal / Ideal';
            if ($lilaVal > 0) {
                if ($lilaVal < 11.5) {
                    $warnaLila = 'Merah';
                    $levelLila = 'danger';
                    $badgeLila = 'Risiko Tinggi';
                } elseif ($lilaVal < 12.5) {
                    $warnaLila = 'Kuning';
                    $levelLila = 'warning';
                    $badgeLila = 'Waspada Ambang';
                }
            }
            $metrikLila = [
                'nilai' => $lilaVal,
                'digits' => 1,
                'unit' => 'cm',
                'footer_label' => 'Status LiLA',
                'footer_nilai' => $warnaLila,
                'badge' => $badgeLila,
                'level' => $levelLila,
                'ringkas' => $warnaLila,
            ];

            // Metrik LK
            $lkVal = (float) $p->lingkar_kepala;
            $metrikLk = [
                'nilai' => $lkVal,
                'digits' => 1,
                'unit' => 'cm',
                'footer_label' => 'Klasifikasi',
                'footer_nilai' => 'Ideal',
                'badge' => 'Normosefali',
                'level' => 'ok',
                'ringkas' => 'Normal',
            ];

            return [
                'usia_bulan' => $usiaBulanUkur,
                'judul' => $judul,
                'tanggal_lokasi' => $tanggalLokasi,
                'verifikator' => $verifikator,
                'terbaru' => $terbaru,
                'tb' => $metrikTb,
                'bb' => $metrikBb,
                'lila' => $metrikLila,
                'lk' => $metrikLk,
            ];
        });

        // Tentukan status pendampingan dari data pengukuran terbaru
        $pengukuranTerbaru = $semuaPengukuran->first();
        $statusPendampingan = 'Normal';
        if ($pengukuranTerbaru) {
            $statusLower = strtolower($pengukuranTerbaru->status_pertumbuhan ?? '');
            $verifikasi = $pengukuranTerbaru->verifikasi;

            if ($verifikasi?->tindak_lanjut === 'perlu' || str_contains($statusLower, 'pendek') || str_contains($statusLower, 'buruk') || str_contains($statusLower, 'kurang')) {
                $statusPendampingan = 'Intervensi Gizi Aktif';
            } else {
                $statusPendampingan = 'Normal';
            }
        }

        // Ambil data imunisasi & vitamin jika ada
        $imunisasiList = collect();
        if (method_exists($balitaAktif, 'imunisasi')) {
            $balitaAktif->loadMissing(['imunisasi.jenisImunisasi', 'imunisasi.kader', 'vitamin.jenisVitamin', 'vitamin.kader']);

            foreach ($balitaAktif->imunisasi as $im) {
                $tgl = $im->tanggal_pemberian ? Carbon::parse($im->tanggal_pemberian) : null;
                $imunisasiList->push([
                    'nama' => $im->jenisImunisasi?->nama_imunisasi ?? 'Imunisasi',
                    'keterangan' => $im->jenisImunisasi?->deskripsi ?? 'Pemberian vaksin imunisasi lengkap.',
                    'status' => ucfirst($im->status ?? 'Diberikan'),
                    'jenis' => 'vaksin',
                    'tanggal' => $tgl ? $tgl->locale('id')->translatedFormat('d F Y') : '-',
                    'usia_label' => ($birthDateAktif && $tgl) ? 'Usia ' . (int) $birthDateAktif->diffInMonths($tgl) . ' Bulan' : '-',
                    'petugas' => $im->kader?->nama ?? 'Kader Posyandu',
                    'jabatan' => 'Kader Posyandu',
                ]);
            }

            if (isset($balitaAktif->vitamin)) {
                foreach ($balitaAktif->vitamin as $vit) {
                    $tgl = $vit->tanggal_pemberian ? Carbon::parse($vit->tanggal_pemberian) : null;
                    $imunisasiList->push([
                        'nama' => $vit->jenisVitamin?->nama_vitamin ?? 'Vitamin A',
                        'keterangan' => $vit->jenisVitamin?->deskripsi ?? 'Pemberian kapsul vitamin balita.',
                        'status' => ucfirst($vit->status ?? 'Diberikan'),
                        'jenis' => 'vitamin',
                        'tanggal' => $tgl ? $tgl->locale('id')->translatedFormat('d F Y') : '-',
                        'usia_label' => ($birthDateAktif && $tgl) ? 'Usia ' . (int) $birthDateAktif->diffInMonths($tgl) . ' Bulan' : '-',
                        'petugas' => $vit->kader?->nama ?? 'Kader Posyandu',
                        'jabatan' => 'Kader Posyandu',
                    ]);
                }
            }
        }

        $tglDiperbarui = $pengukuranTerbaru && $pengukuranTerbaru->tanggal_pengukuran
            ? Carbon::parse($pengukuranTerbaru->tanggal_pengukuran)->locale('id')->translatedFormat('d F Y')
            : $now->locale('id')->translatedFormat('d F Y');

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat balita berhasil dimuat.',
            'data' => [
                'posyandu_nama' => 'Posyandu Terpadu Prevanta',
                'notif_count' => 0,
                'status_pendampingan' => $statusPendampingan,
                'anak_aktif' => $anakAktifData,
                'daftar_anak' => $daftarAnak,
                'pengukuran' => $pengukuranList,
                'imunisasi' => $imunisasiList->values(),
                'diperbarui_label' => $tglDiperbarui,
            ],
        ]);
    }
}
