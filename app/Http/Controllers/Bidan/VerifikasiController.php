<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Verifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function index(): View
    {
        $balita = Balita::whereHas('pengukuran', function ($query) {
            $query->whereDoesntHave('verifikasi')
                ->orWhereHas('verifikasi', function ($q) {
                    $q->where('status', 'menunggu');
                });
        })->with([
            'orangTua.user',
            'pengukuran' => function ($query) {
                $query->whereDoesntHave('verifikasi')
                    ->orWhereHas('verifikasi', function ($q) {
                        $q->where('status', 'menunggu');
                    })
                    ->latest('tanggal_pengukuran');
            },
        ])->get();

        $countNormal = 0;
        $countPendek = 0;
        $countSangatPendek = 0;

        foreach ($balita as $item) {
            $pengukuran = $item->pengukuran->first();
            $item->latest_pengukuran = $pengukuran;

            if (! $pengukuran) {
                continue;
            }

            $status = strtolower($pengukuran->status_pertumbuhan ?? '');

            if (str_contains($status, 'sangat pendek') || str_contains($status, 'sangat kurus')) {
                $item->status_key = 'danger';
                $item->highlight_label = 'Perlu Rujukan';
                $countSangatPendek++;
            } elseif (
                str_contains($status, 'pendek') ||
                str_contains($status, 'kurus') ||
                str_contains($status, 'kurang')
            ) {
                $item->status_key = 'warning';
                $item->highlight_label = 'Perlu Pemantauan';
                $countPendek++;
            } elseif (
                str_contains($status, 'tinggi') ||
                str_contains($status, 'gemuk') ||
                str_contains($status, 'lebih')
            ) {
                $item->status_key = 'info';
                $item->highlight_label = 'Perlu Pemantauan';
                $countPendek++;
            } elseif ($status === 'normal') {
                $item->status_key = 'success';
                $item->highlight_label = 'Sesuai KMS';
                $countNormal++;
            } else {
                $item->status_key = null;
                $item->highlight_label = 'Pemantauan Rutin';
            }
        }

        return view('bidan.verifikasi', compact(
            'balita', 'countNormal', 'countPendek', 'countSangatPendek'
        ));
    }

    public function store(Request $request, int|string $pengukuranId): RedirectResponse
    {
        $validated = $request->validate([
            'tindak_lanjut' => ['required', 'in:tidak_perlu,perlu'],
            'catatan_penyuluhan' => ['nullable', 'string', 'max:5000'],
        ]);

        $pengukuran = Pengukuran::findOrFail($pengukuranId);

        Verifikasi::updateOrCreate(
            ['pengukuran_id' => $pengukuran->id],
            [
                'bidan_id' => Auth::id() ?? 1,
                'tanggal_verifikasi' => now('Asia/Jakarta'),
                'status' => 'terverifikasi',
                'tindak_lanjut' => $validated['tindak_lanjut'],
                'catatan_penyuluhan' => $validated['catatan_penyuluhan'] ?? null,
            ]
        );

        return redirect()
            ->back()
            ->with('success', 'Hasil pengukuran balita '.$pengukuran->balita->nama.' berhasil diverifikasi.');
    }
}
