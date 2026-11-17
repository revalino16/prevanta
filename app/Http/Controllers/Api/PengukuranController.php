<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengukuranRequest;
use App\Models\Balita;
use App\Models\ImunisasiBalita;
use App\Models\JenisImunisasi;
use App\Models\JenisVitamin;
use App\Models\Pengukuran;
use App\Models\VitaminBalita;
use App\Support\AnthropometricMeasurementLimits;
use App\Support\HeightForAgeZScoreCalculator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengukuranController extends Controller
{
    public function options(Request $request): JsonResponse
    {
        $balitaId = $request->integer('balita_id');
        $sudahDiukurBulanIni = $balitaId > 0 && Pengukuran::query()
            ->where('balita_id', $balitaId)
            ->whereYear('tanggal_pengukuran', now('Asia/Jakarta')->year)
            ->whereMonth('tanggal_pengukuran', now('Asia/Jakarta')->month)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'imunisasi' => JenisImunisasi::query()
                    ->orderBy('nama_imunisasi')
                    ->get(['id', 'nama_imunisasi']),
                'vitamin' => JenisVitamin::query()
                    ->orderBy('nama_vitamin')
                    ->get(['id', 'nama_vitamin']),
                'batas' => AnthropometricMeasurementLimits::all(),
                'tanggal_pengukuran' => now('Asia/Jakarta')->toDateString(),
                'sudah_diukur_bulan_ini' => $sudahDiukurBulanIni,
            ],
        ]);
    }

    public function store(
        StorePengukuranRequest $request,
        Balita $balita,
        HeightForAgeZScoreCalculator $calculator,
    ): JsonResponse {
        $validated = $request->validated();
        $birthDate = Carbon::parse($balita->tanggal_lahir);
        $measurementDate = Carbon::createFromFormat('Y-m-d', $validated['tanggal_pengukuran']);
        $position = $birthDate->diffInDays($measurementDate) <= 730 ? 'terlentang' : 'berdiri';
        $zScore = $calculator->calculate(
            $balita->jenis_kelamin,
            $birthDate,
            $measurementDate,
            (float) $validated['tinggi_badan'],
            $position,
        );
        $measurement = null;

        DB::transaction(function () use ($request, $balita, $validated, $calculator, $zScore, $position, &$measurement): void {
            $kaderId = (int) $request->user()->getAuthIdentifier();
            $measurement = Pengukuran::create([
                'balita_id' => $balita->getKey(),
                'kader_id' => $kaderId,
                'tanggal_pengukuran' => $validated['tanggal_pengukuran'],
                'berat_badan' => $validated['berat_badan'],
                'tinggi_badan' => $validated['tinggi_badan'],
                'posisi_pengukuran' => $position,
                'lingkar_lengan_atas' => $validated['lingkar_lengan_atas'],
                'lingkar_kepala' => $validated['lingkar_kepala'],
                'z_score' => $zScore,
                'status_pertumbuhan' => $calculator->status($zScore),
            ]);

            if (! empty($validated['jenis_imunisasi_id'])) {
                ImunisasiBalita::create([
                    'balita_id' => $balita->getKey(),
                    'jenis_imunisasi_id' => $validated['jenis_imunisasi_id'],
                    'kader_id' => $kaderId,
                    'tanggal_pemberian' => $validated['tanggal_pengukuran'],
                    'status' => 'sudah_diberikan',
                ]);
            }

            if (! empty($validated['jenis_vitamin_id'])) {
                VitaminBalita::create([
                    'balita_id' => $balita->getKey(),
                    'jenis_vitamin_id' => $validated['jenis_vitamin_id'],
                    'kader_id' => $kaderId,
                    'tanggal_pemberian' => $validated['tanggal_pengukuran'],
                    'status' => 'sudah_diberikan',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengukuran berhasil disimpan. Z-Score dihitung otomatis.',
            'data' => [
                'id' => $measurement?->id,
                'z_score' => $zScore,
                'status_pertumbuhan' => $calculator->status($zScore),
            ],
        ], 201);
    }
}
