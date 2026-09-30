<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now('Asia/Jakarta');

        // Jadwal mendatang (hari ini + ke depan)
        $jadwalMendatang = Jadwal::where('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal')
            ->get();

        // Jadwal yang sudah lewat (bulan ini)
        $jadwalLewat = Jadwal::where('tanggal', '<', $now->toDateString())
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->orderByDesc('tanggal')
            ->get();

        // Jadwal hari ini
        $jadwalHariIni = Jadwal::whereDate('tanggal', $now->toDateString())
            ->orderBy('jenis_kegiatan')
            ->get();

        // Statistik bulan ini
        $totalBulanIni = Jadwal::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->count();

        $sudahLewat = Jadwal::where('tanggal', '<', $now->toDateString())
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->count();

        return view('kader.jadwal', compact(
            'jadwalMendatang',
            'jadwalLewat',
            'jadwalHariIni',
            'totalBulanIni',
            'sudahLewat',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'jenis_kegiatan' => 'required|string|max:100|regex:/^[a-zA-Z0-9\s]+$/',
            'tanggal' => 'required|date|after_or_equal:today',
            'lokasi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ], [
            'jenis_kegiatan.regex' => 'Nama atau jenis kegiatan tidak boleh mengandung simbol.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh hari yang sudah lewat.',
            'tanggal.before_or_equal' => 'Tanggal hanya bisa untuk 7 hari ke depan.',
        ]);

        Jadwal::create([
            'users_id' => Auth::id(),
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'tanggal' => $request->tanggal,
            'lokasi' => $request->lokasi,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('kader.jadwal')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        Jadwal::findOrFail($id)->delete();

        return redirect()->route('kader.jadwal')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}
