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
     * Mengambil daftar data pengukuran yang perlu diverifikasi oleh bidan.
     * Setiap pengukuran (meskipun balitanya sama) dibuat sebagai 1 item/card terpisah,
     * dan diurutkan berdasarkan tanggal pengukuran terlama (FIFO).
     */
    public function verifikasi(): JsonResponse
    {
        // Ambil data pengukuran yang belum diverifikasi atau status verifikasi masih 'menunggu'
        // Diurutkan berdasarkan tanggal pengukuran terlama (terdahulu lebih dulu diverifikasi)
        $pengukuranList = Pengukuran::whereDoesntHave('verifikasi')
            ->orWhereHas('verifikasi', function ($q) {
                $q->where('status', 'menunggu');
            })
            ->with(['balita.orangTua.user', 'verifikasi'])
            ->orderBy('tanggal_pengukuran', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $data = $pengukuranList->map(function ($p) {
            $b = $p->balita;
            $verif = $p->verifikasi;
            $now = Carbon::now('Asia/Jakarta');
            $umurBulan = ($b && $b->tanggal_lahir) ? Carbon::parse($b->tanggal_lahir)->diffInMonths($now) : 0;

            return [
                'id' => (string) ($b?->id ?? ''),
                'nama' => $b?->nama ?? '-',
                'nama_ibu' => $b?->orangTua?->user?->nama ?? '-',
                'nik' => $b?->nik ?? '-',
                'umur_bulan' => $umurBulan,
                'jenis_kelamin' => strtoupper($b?->jenis_kelamin ?? 'L'),
                'tanggal_ukur' => $p->tanggal_pengukuran ? Carbon::parse($p->tanggal_pengukuran)->isoFormat('DD MMM YYYY') : '-',
                'pb_tb' => (float) ($p->tinggi_badan ?? 0),
                'bb' => (float) ($p->berat_badan ?? 0),
                'lila' => (float) ($p->lingkar_lengan_atas ?? 0),
                'z_score' => (float) ($p->z_score ?? 0),
                'status_pertumbuhan' => $p->status_pertumbuhan ?? 'normal',
                'sudah_sah' => $verif ? ($verif->status === 'terverifikasi') : false,
                'pengukuran_id' => $p->id,
                'alamat' => $b?->alamat,
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
