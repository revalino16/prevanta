<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\OrangTua;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalitaController extends Controller
{
    public function index(): View
    {
        $balita = Balita::with([
            'orangTua.user',
            'pengukuran' => function ($query) {
                $query->whereHas('verifikasi', function ($q) {
                    $q->where('status', 'terverifikasi');
                })->latest('tanggal_pengukuran');
            },
        ])->get();

        $countNormal = 0;
        $countPendek = 0;
        $countSangatPendek = 0;

        foreach ($balita as $item) {
            $pengukuran = $item->pengukuran->first();
            $item->latest_pengukuran = $pengukuran;

            if (! $pengukuran) {
                $item->status_key = null;
                $item->highlight_label = 'Belum Ada Data Terverifikasi';

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

        return view('kader.monitoringbalita', compact(
            'balita',
            'countNormal',
            'countPendek',
            'countSangatPendek'
        ));
    }

    public function create(): View
    {
        $orangTua = OrangTua::with('user')->get();

        return view('kader.create', compact('orangTua'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'orang_tua_id' => 'required|exists:orang_tua,id',
            'nama' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'nik' => 'required|digits:16|unique:balita,nik',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
        ], [
            'nama.regex' => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        Balita::create($validated);

        return redirect()
            ->route('kader.monitoringbalita')
            ->with('success', 'Data balita berhasil ditambahkan.');
    }

    public function edit(int|string $id): View
    {
        $balita = Balita::findOrFail($id);
        $orangTua = OrangTua::with('user')->get();

        return view('kader.edit', compact('balita', 'orangTua'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        $request->validate([
            'orang_tua_id' => 'required|exists:orang_tua,id',
            'nama' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'nik' => 'required|digits:16|unique:balita,nik,' . $id,
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
        ], [
            'nama.regex' => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $balita->update([
            'orang_tua_id' => $request->orang_tua_id,
            'nama' => $request->nama,
            'nik' => $request->nik,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
        ]);

        return redirect()
            ->route('kader.monitoringbalita')
            ->with('success', 'Data balita berhasil diubah.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);
        $balita->delete();

        return redirect()
            ->route('kader.monitoringbalita')
            ->with('success', 'Data balita berhasil dihapus.');
    }

    public function unduhPdf()
    {
        $balita = Balita::with([
            'orangTua.user',
            'pengukuran' => function ($query) {
                $query->whereHas('verifikasi', function ($q) {
                    $q->where('status', 'terverifikasi');
                })->latest('tanggal_pengukuran');
            },
        ])->get();

        foreach ($balita as $item) {
            $item->latest_pengukuran = $item->pengukuran->first();
        }

        $pdf = Pdf::loadView('kader.laporanBalitaPDF', compact('balita'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rekap-monitoring-balita.pdf');
    }
}
