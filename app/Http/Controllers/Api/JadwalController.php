<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    /**
     * GET /api/jadwal
     * Mengambil daftar jadwal kegiatan posyandu dan ringkasan statistik.
     * Identik dengan App\Http\Controllers\Kader\JadwalController@index
     */
    public function index(): JsonResponse
    {
        $now = Carbon::now('Asia/Jakarta');

        // Jadwal mendatang (hari ini + ke depan)
        $jadwalMendatang = Jadwal::where('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        // Jadwal yang sudah lewat (bulan ini)
        $jadwalLewat = Jadwal::where('tanggal', '<', $now->toDateString())
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->orderByDesc('tanggal')
            ->get();

        // Jadwal hari ini
        $jadwalHariIni = Jadwal::whereDate('tanggal', $now->toDateString())
            ->orderBy('jenis_kegiatan', 'asc')
            ->get();

        // Statistik bulan ini
        $totalBulanIni = Jadwal::whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->count();

        $sudahLewat = Jadwal::where('tanggal', '<', $now->toDateString())
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->count();

        $formatJadwal = fn ($j) => [
            'id'             => $j->id,
            'users_id'       => $j->users_id,
            'jenis_kegiatan' => $j->jenis_kegiatan,
            'tanggal'        => $j->tanggal ? Carbon::parse($j->tanggal)->format('Y-m-d') : null,
            'lokasi'         => $j->lokasi,
            'keterangan'     => $j->keterangan,
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'statistik' => [
                    'total_bulan_ini' => $totalBulanIni,
                    'sudah_lewat'     => $sudahLewat,
                ],
                'jadwal_hari_ini'   => $jadwalHariIni->map($formatJadwal),
                'jadwal_mendatang'  => $jadwalMendatang->map($formatJadwal),
                'jadwal_lewat'      => $jadwalLewat->map($formatJadwal),
            ],
        ]);
    }

    /**
     * POST /api/jadwal
     * Menambahkan jadwal kegiatan baru.
     * Identik dengan App\Http\Controllers\Kader\JadwalController@store
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jenis_kegiatan' => 'required|string|max:100|regex:/^[a-zA-Z0-9\s]+$/',
            'tanggal'        => 'required|date|after_or_equal:today',
            'lokasi'         => 'nullable|string|max:255',
            'keterangan'     => 'nullable|string',
        ], [
            'jenis_kegiatan.regex'   => 'Nama atau jenis kegiatan tidak boleh mengandung simbol.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh hari yang sudah lewat.',
        ]);

        $jadwal = Jadwal::create([
            'users_id'       => $request->user()?->id ?? Auth::id(),
            'jenis_kegiatan' => $validated['jenis_kegiatan'],
            'tanggal'        => $validated['tanggal'],
            'lokasi'         => $validated['lokasi'] ?? null,
            'keterangan'     => $validated['keterangan'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan.',
            'data'    => $jadwal,
        ], 201);
    }

    /**
     * GET /api/jadwal/{id}
     * Detail satu jadwal kegiatan.
     */
    public function show(int|string $id): JsonResponse
    {
        $jadwal = Jadwal::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $jadwal->id,
                'users_id'       => $jadwal->users_id,
                'jenis_kegiatan' => $jadwal->jenis_kegiatan,
                'tanggal'        => $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->format('Y-m-d') : null,
                'lokasi'         => $jadwal->lokasi,
                'keterangan'     => $jadwal->keterangan,
            ],
        ]);
    }

    /**
     * PUT/PATCH /api/jadwal/{id}
     * Memperbarui jadwal kegiatan.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $jadwal = Jadwal::findOrFail($id);

        $validated = $request->validate([
            'jenis_kegiatan' => 'sometimes|string|max:100|regex:/^[a-zA-Z0-9\s]+$/',
            'tanggal'        => 'sometimes|date',
            'lokasi'         => 'nullable|string|max:255',
            'keterangan'     => 'nullable|string',
        ], [
            'jenis_kegiatan.regex' => 'Nama atau jenis kegiatan tidak boleh mengandung simbol.',
        ]);

        $jadwal->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui.',
            'data'    => $jadwal,
        ]);
    }

    /**
     * DELETE /api/jadwal/{id}
     * Menghapus jadwal kegiatan.
     * Identik dengan App\Http\Controllers\Kader\JadwalController@destroy
     */
    public function destroy(int|string $id): JsonResponse
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }
}
