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

            $statusPertumbuhan = $pengukuran->status_pertumbuhan;

            [$item->status_key, $item->highlight_label] = match ($statusPertumbuhan) {
                'sangat pendek' => ['danger', 'Perlu Rujukan'],
                'pendek' => ['warning', 'Perlu Pemantauan'],
                'normal' => ['success', 'Sesuai KMS'],
                default => [null, 'Pemantauan Rutin'],
            };

            match ($statusPertumbuhan) {
                'sangat pendek' => $countSangatPendek++,
                'pendek' => $countPendek++,
                'normal' => $countNormal++,
                default => null,
            };
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
