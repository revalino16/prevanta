<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Verifikasi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidanController extends Controller
{
    /**
     * GET /api/bidan/verifikasi
     * Mengambil daftar balita beserta status verifikasi pengukurannya.
     */
    public function verifikasi(): JsonResponse
    {
        $balitaList = Balita::with([
            'orangTua.user',
            'pengukuran' => function ($q) {
                $q->with('verifikasi')->latest('tanggal_pengukuran');
            },
        ])->get();

        $data = $balitaList->map(function ($b) {
            $latestPengukuran = $b->pengukuran->first();
            $verif = $latestPengukuran?->verifikasi;
            $now = Carbon::now('Asia/Jakarta');
            $umurBulan = $b->tanggal_lahir ? Carbon::parse($b->tanggal_lahir)->diffInMonths($now) : 0;

            return [
                'id' => (string) $b->id,
                'nama' => $b->nama,
                'nama_ibu' => $b->orangTua?->user?->nama ?? '-',
                'nik' => $b->nik,
                'umur_bulan' => $umurBulan,
                'jenis_kelamin' => strtoupper($b->jenis_kelamin ?? 'L'),
                'tanggal_ukur' => $latestPengukuran ? Carbon::parse($latestPengukuran->tanggal_pengukuran)->isoFormat('DD MMM YYYY') : '-',
                'pb_tb' => (float) ($latestPengukuran?->tinggi_badan ?? 0),
                'bb' => (float) ($latestPengukuran?->berat_badan ?? 0),
                'lila' => (float) ($latestPengukuran?->lingkar_lengan_atas ?? 0),
                'z_score' => (float) ($latestPengukuran?->z_score ?? 0),
                'status_pertumbuhan' => $latestPengukuran?->status_pertumbuhan ?? 'normal',
                'sudah_sah' => $verif ? ($verif->status === 'terverifikasi') : false,
                'pengukuran_id' => $latestPengukuran?->id,
                'alamat' => $b->alamat,
                'catatan_penyuluhan' => $verif?->catatan_penyuluhan,
                'tindak_lanjut' => $verif?->tindak_lanjut,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POST /api/bidan/verifikasi/{pengukuranId}
     * Menyimpan hasil verifikasi pengukuran balita.
     */
    public function storeVerifikasi(Request $request, int|string $pengukuranId): JsonResponse
    {
        $validated = $request->validate([
            'tindak_lanjut' => ['required', 'in:tidak_perlu,perlu'],
            'catatan_penyuluhan' => ['nullable', 'string', 'max:5000'],
        ]);

        $pengukuran = Pengukuran::findOrFail($pengukuranId);

        $verifikasi = Verifikasi::updateOrCreate(
            ['pengukuran_id' => $pengukuran->id],
            [
                'bidan_id' => Auth::id() ?? 8,
                'tanggal_verifikasi' => now('Asia/Jakarta'),
                'status' => 'terverifikasi',
                'tindak_lanjut' => $validated['tindak_lanjut'],
                'catatan_penyuluhan' => $validated['catatan_penyuluhan'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Hasil pengukuran berhasil diverifikasi.',
            'data' => $verifikasi,
        ]);
    }
}
