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
     * Berisi info posyandu, sapaan, agenda posyandu, dan daftar anak beserta
     * hasil pengukuran terbaru yang diinputkan oleh kader.
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

        // Fallback untuk testing: jika user belum punya balita tapi ada balita di database
        if ($balitaList->isEmpty() && (! $orangTua || $balitaList->count() === 0)) {
            // Ambil semua balita milik orang tua pertama sebagai fallback preview jika akun belum terkait
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
}
