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
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'nama.regex' => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'foto_profil.image' => 'File foto profil harus berupa gambar.',
            'foto_profil.mimes' => 'Format foto profil harus JPG, PNG, atau WebP.',
            'foto_profil.max' => 'Ukuran foto profil maksimal 3 MB.',
        ]);

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/balita');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $validated['foto_profil'] = 'uploads/balita/' . $filename;
        }

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

        $validated = $request->validate([
            'orang_tua_id' => 'required|exists:orang_tua,id',
            'nama' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'nik' => 'required|digits:16|unique:balita,nik,'.$id,
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'nama.regex' => 'Nama balita hanya boleh berisi huruf, spasi, titik, atau tanda kutip (tidak boleh ada simbol atau angka).',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'foto_profil.image' => 'File foto profil harus berupa gambar.',
            'foto_profil.mimes' => 'Format foto profil harus JPG, PNG, atau WebP.',
            'foto_profil.max' => 'Ukuran foto profil maksimal 3 MB.',
        ]);

        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($balita->foto_profil && file_exists(public_path($balita->foto_profil))) {
                @unlink(public_path($balita->foto_profil));
            }
            $file = $request->file('foto_profil');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/balita');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $validated['foto_profil'] = 'uploads/balita/' . $filename;
        }

        $balita->update($validated);

        return redirect()
            ->route('kader.monitoringbalita')
            ->with('success', 'Data balita berhasil diubah.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $balita = Balita::findOrFail($id);

        if ($balita->foto_profil && file_exists(public_path($balita->foto_profil))) {
            @unlink(public_path($balita->foto_profil));
        }

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
