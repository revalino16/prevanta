<?php

namespace App\Http\Controllers\Kader;

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PengukuranController extends Controller
{
    public function create(Balita $balita): View
    {
        $balita->load('orangTua.user');
        $birthDate = Carbon::parse($balita->tanggal_lahir);
        $today = now('Asia/Jakarta')->startOfDay();
        $maximumMeasurementDate = $birthDate->copy()->addYears(5)->min($today);

        return view('kader.pengukuran-create', [
            'balita' => $balita,
            'birthDateLabel' => $birthDate->locale('id')->translatedFormat('d F Y'),
            'currentAgeLabel' => $birthDate->locale('id')->diffForHumans($today, [
                'parts' => 2,
                'join' => true,
                'syntax' => Carbon::DIFF_ABSOLUTE,
            ]),
            'childCode' => 'BLT-'.str_pad((string) $balita->getKey(), 3, '0', STR_PAD_LEFT),
            'latestMeasurement' => $balita->pengukuran()
                ->orderByDesc('tanggal_pengukuran')
                ->orderByDesc('id')
                ->first(),
            'immunizationTypes' => JenisImunisasi::query()->orderBy('nama_imunisasi')->get(),
            'vitaminTypes' => JenisVitamin::query()->orderBy('nama_vitamin')->get(),
            'defaultMeasurementDate' => $today->toDateString(),
            'maximumMeasurementDate' => $maximumMeasurementDate->toDateString(),
            'measurementLimits' => AnthropometricMeasurementLimits::all(),
        ]);
    }

    public function store(
        StorePengukuranRequest $request,
        Balita $balita,
        HeightForAgeZScoreCalculator $calculator,
    ): RedirectResponse {
        $validated = $request->validated();
        $birthDate = Carbon::parse($balita->tanggal_lahir);
        $measurementDate = Carbon::createFromFormat('Y-m-d', $validated['tanggal_pengukuran']);
        $posisiPengukuran = $birthDate->diffInDays($measurementDate) <= 730 ? 'terlentang' : 'berdiri';

        $zScore = $calculator->calculate(
            $balita->jenis_kelamin,
            $birthDate,
            $measurementDate,
            (float) $validated['tinggi_badan'],
            $posisiPengukuran,
        );
        $photoPath = null;

        try {
            if ($request->hasFile('foto_pertumbuhan')) {
                $photoPath = $request->file('foto_pertumbuhan')?->store('pengukuran-balita', 'public');

                if ($photoPath === false) {
                    throw new RuntimeException('Gambar pengukuran tidak dapat disimpan.');
                }
            }

            DB::transaction(function () use ($validated, $balita, $calculator, $zScore, $photoPath, $request, $posisiPengukuran): void {
                $kaderId = (int) $request->user()->getAuthIdentifier();

                Pengukuran::create([
                    'balita_id' => $balita->getKey(),
                    'kader_id' => $kaderId,
                    'tanggal_pengukuran' => $validated['tanggal_pengukuran'],
                    'berat_badan' => $validated['berat_badan'],
                    'tinggi_badan' => $validated['tinggi_badan'],
                    'posisi_pengukuran' => $posisiPengukuran,
                    'lingkar_lengan_atas' => $validated['lingkar_lengan_atas'],
                    'lingkar_kepala' => $validated['lingkar_kepala'],
                    'z_score' => $zScore,
                    'status_pertumbuhan' => $calculator->status($zScore),
                    'foto_pertumbuhan' => $photoPath,
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
        } catch (Throwable $exception) {
            if (is_string($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('kader.balita.kms', $balita)
            ->with('success', 'Pengukuran balita berhasil disimpan dan Z-score telah dihitung otomatis.');
    }
}
