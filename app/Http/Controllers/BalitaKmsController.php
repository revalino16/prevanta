<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\ImunisasiBalita;
use App\Models\Pengukuran;
use App\Models\Verifikasi;
use App\Models\VitaminBalita;
use App\Support\HeightForAgeZScoreCalculator;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class BalitaKmsController extends Controller
{
    private const CHART_LEFT = 68;

    private const CHART_RIGHT = 860;

    private const CHART_TOP = 28;

    private const CHART_BOTTOM = 318;

    public function __invoke(Balita $balita): View
    {
        $balita->load([
            'pengukuran' => fn($query) => $query->oldest('tanggal_pengukuran'),
            'pengukuran.kader',
            'pengukuran.verifikasi',
            'pengukuran.verifikasi.bidan',
            'imunisasi' => fn($query) => $query->latest('tanggal_pemberian'),
            'imunisasi.jenisImunisasi',
            'imunisasi.kader',
            'vitamin' => fn($query) => $query->latest('tanggal_pemberian'),
            'vitamin.jenisVitamin',
            'vitamin.kader',
        ]);

        $birthDate = Carbon::parse($balita->tanggal_lahir);

        $verifiedMeasurements = $balita->pengukuran
            ->filter(fn(Pengukuran $m) => $m->verifikasi?->status === 'terverifikasi')
            ->values();

        $measurementHistory = $verifiedMeasurements
            ->map(fn(Pengukuran $measurement): array => $this->measurementCard($measurement, $birthDate));

        $healthHistory = $this->healthHistory($balita, $birthDate);

        $view = request()->user()?->role === 'orang_tua'
            ? 'orangtua.profil-balita'
            : 'kader.profil-balita';

        return view($view, [
            'balita' => $balita,
            'birthDateLabel' => $this->dateLabel($birthDate),
            'currentAgeLabel' => $this->ageLabel($birthDate, now('Asia/Jakarta')),
            'childCode' => 'BLT-' . str_pad((string) $balita->getKey(), 3, '0', STR_PAD_LEFT),
            'latestMeasurement' => $measurementHistory->last(),
            'measurementHistory' => $measurementHistory->reverse()->values(),
            'growthCharts' => $this->growthCharts($verifiedMeasurements, $birthDate, (string) ($balita->jenis_kelamin ?? 'L')),
            'healthHistory' => $healthHistory,
            'healthLastUpdated' => $healthHistory->first()['date'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function measurementCard(Pengukuran $measurement, Carbon $birthDate): array
    {
        $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
        $tone = $this->statusTone($measurement->status_pertumbuhan);
        $verification = $measurement->verifikasi;

        return [
            'id' => (int) $measurement->getKey(),
            'date' => $this->dateLabel($measurementDate),
            'age' => $this->healthAgeLabel($birthDate, $measurementDate),
            'weight' => $this->measurementValue($measurement->berat_badan),
            'height' => $this->measurementValue($measurement->tinggi_badan),
            'headCircumference' => $this->measurementValue($measurement->lingkar_kepala),
            'armCircumference' => $this->measurementValue($measurement->lingkar_lengan_atas),
            'zScore' => $this->signedValue($measurement->z_score),
            'status' => $this->healthStatus($measurement->status_pertumbuhan),
            'tone' => $tone,
            'followUp' => $this->followUpLabel($tone, $verification),
            'counselingNote' => $verification?->catatan_penyuluhan,
            'verificationLabel' => $this->verificationLabel($verification),
            'verificationTone' => $this->verificationTone($verification),
            'counselor' => $verification?->bidan?->nama,
            'companion' => $measurement->kader?->nama,
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, array<string, mixed>>
     */
    private function growthCharts(Collection $measurements, Carbon $birthDate, string $gender = 'L'): array
    {
        return [
            'tb-u' => $this->heightForAgeChart($measurements, $birthDate, $gender),
            'bb-u' => $this->weightForAgeChart($measurements, $birthDate),
            'bb-tb' => $this->weightForHeightChart($measurements),
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, mixed>
     */
    private function heightForAgeChart(Collection $measurements, Carbon $birthDate, string $gender = 'L'): array
    {
        $chartMeasurements = $measurements
            ->filter(fn(Pengukuran $measurement): bool => $measurement->tinggi_badan !== null)
            ->map(function (Pengukuran $measurement) use ($birthDate): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
                $age = max(0, (int) $birthDate->diffInMonths($measurementDate));
                $tb = (float) $measurement->tinggi_badan;

                return [
                    'xValue' => (float) $age,
                    'yValue' => $tb,
                    'tooltip' => $this->dateLabel($measurementDate)
                        . ' — Usia: ' . $age . ' Bulan — TB: ' . $this->measurementValue($tb) . ' cm'
                        . ($measurement->z_score !== null ? ' (Z-Score: ' . $this->signedValue($measurement->z_score) . ' SD)' : ''),
                ];
            })
            ->sortBy('xValue')
            ->values();

        $latestAge = $chartMeasurements->pluck('xValue')->max() ?? 0;
        $maxAge = $latestAge <= 24 ? 24 : 60;
        $ageStep = $maxAge <= 24 ? 3 : 6;

        $minY = 40.0;
        $maxY = $maxAge <= 24 ? 100.0 : 130.0;
        $yStep = 10.0;

        $points = $this->plotPoints($chartMeasurements, 0, $maxAge, $minY, $maxY);

        // Generate WHO Growth Curves & Polygon Zones
        $whoCurveMonths = range(0, $maxAge);
        $curvePlus3 = [];
        $curvePlus2 = [];
        $curveMedian = [];
        $curveMinus2 = [];
        $curveMinus3 = [];

        foreach ($whoCurveMonths as $m) {
            $ref = HeightForAgeZScoreCalculator::getReferenceForMonth($gender, $m);
            $xCoord = $this->scale((float) $m, 0, $maxAge, self::CHART_LEFT, self::CHART_RIGHT);

            $curvePlus3[] = ['x' => $xCoord, 'y' => $this->scale($ref['sd_plus_3'], $minY, $maxY, self::CHART_BOTTOM, self::CHART_TOP)];
            $curvePlus2[] = ['x' => $xCoord, 'y' => $this->scale($ref['sd_plus_2'], $minY, $maxY, self::CHART_BOTTOM, self::CHART_TOP)];
            $curveMedian[] = ['x' => $xCoord, 'y' => $this->scale($ref['sd_0'], $minY, $maxY, self::CHART_BOTTOM, self::CHART_TOP)];
            $curveMinus2[] = ['x' => $xCoord, 'y' => $this->scale($ref['sd_minus_2'], $minY, $maxY, self::CHART_BOTTOM, self::CHART_TOP)];
            $curveMinus3[] = ['x' => $xCoord, 'y' => $this->scale($ref['sd_minus_3'], $minY, $maxY, self::CHART_BOTTOM, self::CHART_TOP)];
        }

        // Polygon points
        // Normal Zone (Hijau): Antara +2 SD dan -2 SD
        $normalPoints = array_merge($curvePlus2, array_reverse($curveMinus2));
        // Warning Zone (Kuning - Stunting): Antara -2 SD dan -3 SD
        $warningPoints = array_merge($curveMinus2, array_reverse($curveMinus3));
        // Danger Zone (Merah - Sangat Pendek / Severely Stunted): Antara -3 SD dan garis bawah chart
        $bottomRight = ['x' => self::CHART_RIGHT, 'y' => self::CHART_BOTTOM];
        $bottomLeft = ['x' => self::CHART_LEFT, 'y' => self::CHART_BOTTOM];
        $dangerPoints = array_merge($curveMinus3, [$bottomRight, $bottomLeft]);

        $curvedZones = [
            ['type' => 'normal', 'points' => $this->polyline($normalPoints)],
            ['type' => 'warning', 'points' => $this->polyline($warningPoints)],
            ['type' => 'danger', 'points' => $this->polyline($dangerPoints)],
        ];

        $whoCurves = [
            ['label' => '+3 SD (Tinggi)', 'short' => '3', 'tone' => 'neutral', 'points' => $this->polyline($curvePlus3), 'end' => end($curvePlus3)],
            ['label' => '+2 SD (Batas Atas Normal)', 'short' => '2', 'tone' => 'green', 'points' => $this->polyline($curvePlus2), 'end' => end($curvePlus2)],
            ['label' => '0 SD (Median Standar WHO)', 'short' => '0', 'tone' => 'median', 'points' => $this->polyline($curveMedian), 'end' => end($curveMedian)],
            ['label' => '-2 SD (Garis Batas Stunting/Pendek)', 'short' => '-2', 'tone' => 'amber', 'points' => $this->polyline($curveMinus2), 'end' => end($curveMinus2)],
            ['label' => '-3 SD (Garis Batas Sangat Pendek)', 'short' => '-3', 'tone' => 'red', 'points' => $this->polyline($curveMinus3), 'end' => end($curveMinus3)],
        ];

        // Sumbu Y ticks (Major per 5 cm, Minor per 1 cm untuk grid milimeter blok)
        $yTicks = [];
        $yMinorGrid = [];
        for ($val = $minY; $val <= $maxY; $val += 1.0) {
            $yCoord = $this->scale($val, $minY, $maxY, self::CHART_BOTTOM, self::CHART_TOP);
            $isMajor = ((int) $val % 5 === 0);
            $yMinorGrid[] = [
                'y' => $yCoord,
                'isMajor' => $isMajor,
                'val' => (int) $val,
            ];
            if ($isMajor) {
                $yTicks[] = [
                    'y' => $yCoord,
                    'label' => (string) (int) $val,
                    'tone' => 'neutral',
                ];
            }
        }

        // Sumbu X ticks (Major per interval ageStep, Minor per 1 bulan)
        $xTicks = [];
        $xMinorGrid = [];
        for ($m = 0; $m <= $maxAge; $m++) {
            $xCoord = $this->scale((float) $m, 0, $maxAge, self::CHART_LEFT, self::CHART_RIGHT);
            $isYear = ($m % 12 === 0);
            $isStep = ($m % $ageStep === 0);

            $xMinorGrid[] = [
                'x' => $xCoord,
                'isYear' => $isYear,
                'isStep' => $isStep,
                'month' => $m,
            ];

            if ($isStep) {
                $label = $m === 0 ? '0' : ($m % 12 === 0 ? ($m / 12) . ' thn (' . $m . ')' : (string) $m);
                $xTicks[] = [
                    'x' => $xCoord,
                    'label' => $label,
                ];
            }
        }

        $genderUpper = strtoupper($gender);
        $genderLabel = $genderUpper === 'P' ? 'Anak Perempuan' : 'Anak Laki-Laki';
        $chartTitle = $maxAge <= 24
            ? 'Panjang Badan Menurut Umur (0 - 24 Bulan)'
            : 'Tinggi Badan Menurut Umur (2 - 5 Tahun)';
        $standardTag = 'Standar Pertumbuhan Anak (WHO Growth Standards)';

        return [
            'label' => 'TB/U',
            'subLabel' => 'Tinggi Badan menurut Umur',
            'chartTitle' => $chartTitle,
            'standardTag' => $standardTag,
            'gender' => $genderUpper,
            'genderLabel' => $genderLabel,
            'description' => 'Grafik pertumbuhan Tinggi/Panjang Badan menurut Umur standar Buku KIA & WHO.',
            'xAxisTitle' => 'Umur (Bulan dan Tahun)',
            'yAxisTitle' => 'Tinggi Badan (cm)',
            'points' => $points,
            'polyline' => $this->polyline($points),
            'xTicks' => $xTicks,
            'xMinorGrid' => $xMinorGrid,
            'yTicks' => array_reverse($yTicks),
            'yMinorGrid' => $yMinorGrid,
            'chartBounds' => [
                'left' => self::CHART_LEFT,
                'right' => self::CHART_RIGHT,
                'top' => self::CHART_TOP,
                'bottom' => self::CHART_BOTTOM,
            ],
            'showsZones' => false,
            'curvedZones' => $curvedZones,
            'whoCurves' => $whoCurves,
            'emptyTitle' => 'Belum ada data tinggi badan',
            'emptyCopy' => 'Grafik akan terisi otomatis setelah pemeriksaan tinggi badan balita dicatat.',
            'legends' => [
                ['tone' => 'line', 'label' => 'Hasil Pengukuran Balita'],
                ['tone' => 'normal', 'label' => 'Normal (-2 s/d +2 SD)'],
                ['tone' => 'warning', 'label' => 'Pendek / Stunting (-3 s/d -2 SD)'],
                ['tone' => 'danger', 'label' => 'Sangat Pendek / Severely Stunted (< -3 SD)'],
            ],
            'note' => 'Standar baku WHO Child Growth Standards (Kemenkes / Buku KIA). Garis -2 SD adalah batas stunting.',
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, mixed>
     */
    private function weightForAgeChart(Collection $measurements, Carbon $birthDate): array
    {
        $chartMeasurements = $measurements
            ->filter(fn(Pengukuran $measurement): bool => $measurement->berat_badan !== null)
            ->map(function (Pengukuran $measurement) use ($birthDate): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
                $age = max(0, (int) $birthDate->diffInMonths($measurementDate));

                return [
                    'xValue' => (float) $age,
                    'yValue' => (float) $measurement->berat_badan,
                    'tooltip' => $this->dateLabel($measurementDate)
                        . ' — usia ' . $age . ' bulan — BB ' . $this->measurementValue($measurement->berat_badan) . ' kg',
                ];
            })
            ->values();

        [$maxAge, $ageStep] = $this->ageBounds($chartMeasurements->pluck('xValue'));
        [$minimumWeight, $maximumWeight] = $this->valueBounds($chartMeasurements->pluck('yValue'), 2, 0);
        $points = $this->plotPoints($chartMeasurements, 0, $maxAge, $minimumWeight, $maximumWeight);

        return [
            'label' => 'BB/U',
            'subLabel' => 'Berat/Umur',
            'description' => 'Tren berat badan aktual menurut usia pada setiap pemeriksaan.',
            'points' => $points,
            'polyline' => $this->polyline($points),
            'xTicks' => $this->ageTicks($maxAge, $ageStep),
            'yTicks' => $this->valueTicks($minimumWeight, $maximumWeight, 'kg'),
            'showsZones' => false,
            'emptyTitle' => 'Belum ada data berat badan',
            'emptyCopy' => 'Grafik BB/U akan terisi setelah berat badan dicatat.',
            'legends' => [
                ['tone' => 'line', 'label' => 'Berat badan anak'],
                ['tone' => 'recorded', 'label' => 'Data pengukuran tersimpan'],
            ],
            'note' => 'Grafik BB/U menampilkan tren berat aktual. Klasifikasi gizi memerlukan Z-Score BB/U tersendiri.',
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, mixed>
     */
    private function weightForHeightChart(Collection $measurements): array
    {
        $chartMeasurements = $measurements
            ->filter(fn(Pengukuran $measurement): bool => $measurement->berat_badan !== null
                && $measurement->tinggi_badan !== null)
            ->map(function (Pengukuran $measurement): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);

                return [
                    'xValue' => (float) $measurement->tinggi_badan,
                    'yValue' => (float) $measurement->berat_badan,
                    'tooltip' => $this->dateLabel($measurementDate)
                        . ' — TB ' . $this->measurementValue($measurement->tinggi_badan) . ' cm'
                        . ' — BB ' . $this->measurementValue($measurement->berat_badan) . ' kg',
                ];
            })
            ->sortBy('xValue')
            ->values();

        [$minimumHeight, $maximumHeight] = $this->valueBounds($chartMeasurements->pluck('xValue'), 10, 0);
        [$minimumWeight, $maximumWeight] = $this->valueBounds($chartMeasurements->pluck('yValue'), 2, 0);
        $points = $this->plotPoints(
            $chartMeasurements,
            $minimumHeight,
            $maximumHeight,
            $minimumWeight,
            $maximumWeight,
        );

        return [
            'label' => 'BB/TB',
            'subLabel' => 'Berat/Tinggi',
            'description' => 'Perbandingan berat badan aktual terhadap tinggi badan pada setiap pemeriksaan.',
            'points' => $points,
            'polyline' => $this->polyline($points),
            'xTicks' => $this->horizontalValueTicks($minimumHeight, $maximumHeight, 'cm'),
            'yTicks' => $this->valueTicks($minimumWeight, $maximumWeight, 'kg'),
            'showsZones' => false,
            'emptyTitle' => 'Belum ada data perbandingan',
            'emptyCopy' => 'Grafik BB/TB akan terisi setelah berat & tinggi dicatat.',
            'legends' => [
                ['tone' => 'line', 'label' => 'Perbandingan berat dan tinggi anak'],
                ['tone' => 'recorded', 'label' => 'Data pengukuran tersimpan'],
            ],
            'note' => 'Grafik BB/TB menampilkan data aktual. Klasifikasi gizi memerlukan Z-Score BB/TB tersendiri.',
        ];
    }

    /**
     * @param  Collection<int, array{xValue: float, yValue: float}>  $points
     * @return array<int, array{x: float, y: float, tooltip: string}>
     */
    private function plotPoints(
        Collection $points,
        float $minX,
        float $maxX,
        float $minY,
        float $maxY
    ): array {
        return $points->map(function (array $point) use ($minX, $maxX, $minY, $maxY): array {
            return [
                'x' => $this->scale($point['xValue'], $minX, $maxX, self::CHART_LEFT, self::CHART_RIGHT),
                'y' => $this->scale($point['yValue'], $minY, $maxY, self::CHART_BOTTOM, self::CHART_TOP),
                'tooltip' => $point['tooltip'] ?? '',
            ];
        })->all();
    }

    /**
     * @param  array<int, array{x: float, y: float}>  $points
     */
    private function polyline(array $points): string
    {
        return collect($points)
            ->map(fn(array $point): string => "{$point['x']},{$point['y']}")
            ->join(' ');
    }

    /**
     * @return array<int, array{y: float, label: string, tone: string}>
     */
    private function zScoreTicks(): array
    {
        return [
            ['y' => $this->scale(3, -4, 3, self::CHART_BOTTOM, self::CHART_TOP), 'label' => '+3 SD', 'tone' => 'neutral'],
            ['y' => $this->scale(2, -4, 3, self::CHART_BOTTOM, self::CHART_TOP), 'label' => '+2 SD', 'tone' => 'success'],
            ['y' => $this->scale(0, -4, 3, self::CHART_BOTTOM, self::CHART_TOP), 'label' => '0 (Median)', 'tone' => 'success'],
            ['y' => $this->scale(-2, -4, 3, self::CHART_BOTTOM, self::CHART_TOP), 'label' => '-2 SD', 'tone' => 'warning'],
            ['y' => $this->scale(-3, -4, 3, self::CHART_BOTTOM, self::CHART_TOP), 'label' => '-3 SD', 'tone' => 'danger'],
            ['y' => $this->scale(-4, -4, 3, self::CHART_BOTTOM, self::CHART_TOP), 'label' => '-4 SD', 'tone' => 'danger'],
        ];
    }

    /**
     * @return array<int, array{x: float, label: string}>
     */
    private function ageTicks(float $maxAge, float $step): array
    {
        $ticks = [];

        for ($age = 0; $age <= $maxAge; $age += $step) {
            $label = $age === 0 ? '0 bln' : ($age % 12 === 0 ? ($age / 12) . ' thn' : $age . ' bln');
            $ticks[] = [
                'x' => $this->scale($age, 0, $maxAge, self::CHART_LEFT, self::CHART_RIGHT),
                'label' => $label,
            ];
        }

        return $ticks;
    }

    /**
     * @param  Collection<int, float>  $ages
     * @return array{0: float, 1: float}
     */
    private function ageBounds(Collection $ages): array
    {
        $maxAge = max(24, ceil($ages->max() ?? 0));
        $step = 6;

        if ($maxAge > 24) {
            $maxAge = ceil($maxAge / 12) * 12;
            $step = 12;
        }

        return [$maxAge, $step];
    }

    /**
     * @param  Collection<int, float>  $values
     * @return array{0: float, 1: float}
     */
    private function valueBounds(Collection $values, float $buffer, float $minAllowed): array
    {
        if ($values->isEmpty()) {
            return [$minAllowed, $minAllowed + ($buffer * 2)];
        }

        $min = $values->min();
        $max = $values->max();

        $lower = max($minAllowed, floor($min - $buffer));
        $upper = ceil($max + $buffer);

        if ($upper === $lower) {
            $upper += $buffer;
        }

        return [$lower, $upper];
    }

    /**
     * @return array<int, array{y: float, label: string, tone: string}>
     */
    private function valueTicks(float $minimum, float $maximum, string $unit): array
    {
        return collect(range(0, 5))
            ->map(function (int $index) use ($minimum, $maximum, $unit): array {
                $value = $maximum - ((($maximum - $minimum) / 5) * $index);

                return [
                    'y' => round(self::CHART_TOP + (($index / 5) * (self::CHART_BOTTOM - self::CHART_TOP)), 2),
                    'label' => $this->axisValue($value) . ' ' . $unit,
                    'tone' => 'neutral',
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{x: float, label: string}>
     */
    private function horizontalValueTicks(float $minimum, float $maximum, string $unit): array
    {
        return collect(range(0, 5))
            ->map(function (int $index) use ($minimum, $maximum, $unit): array {
                $value = $minimum + ((($maximum - $minimum) / 5) * $index);

                return [
                    'x' => round(self::CHART_LEFT + (($index / 5) * (self::CHART_RIGHT - self::CHART_LEFT)), 2),
                    'label' => $this->axisValue($value) . ' ' . $unit,
                ];
            })
            ->all();
    }

    private function scale(float $value, float $minimum, float $maximum, float $start, float $end): float
    {
        if ($maximum === $minimum) {
            return ($start + $end) / 2;
        }

        $minBound = min($minimum, $maximum);
        $maxBound = max($minimum, $maximum);
        $clampedValue = max($minBound, min($maxBound, $value));

        return $start + ((($clampedValue - $minimum) / ($maximum - $minimum)) * ($end - $start));
    }

    private function axisValue(float $value): string
    {
        return number_format($value, $value === floor($value) ? 0 : 1, ',', '.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function healthHistory(Balita $balita, Carbon $birthDate): Collection
    {
        $immunizations = $balita->imunisasi->map(function (ImunisasiBalita $record) use ($birthDate): array {
            $givenAt = Carbon::parse($record->tanggal_pemberian);

            return [
                'id' => (int) $record->getKey(),
                'type' => 'Imunisasi',
                'tone' => 'immunization',
                'icon' => 'fa-syringe',
                'name' => $record->jenisImunisasi?->nama_imunisasi ?? 'Imunisasi',
                'description' => $record->jenisImunisasi?->deskripsi,
                'date' => $this->dateLabel($givenAt),
                'age' => $this->healthAgeLabel($birthDate, $givenAt),
                'timestamp' => $givenAt->getTimestamp(),
                'status' => $this->healthStatus($record->status),
                'recorder' => $record->kader?->nama ?? 'Kader posyandu',
                'recorderRole' => $this->healthRecorderRole($record->kader?->role),
            ];
        });

        $vitamins = $balita->vitamin->map(function (VitaminBalita $record) use ($birthDate): array {
            $givenAt = Carbon::parse($record->tanggal_pemberian);

            return [
                'id' => (int) $record->getKey(),
                'type' => 'Vitamin',
                'tone' => 'vitamin',
                'icon' => 'fa-prescription-bottle-medical',
                'name' => $record->jenisVitamin?->nama_vitamin ?? 'Vitamin',
                'description' => $record->jenisVitamin?->deskripsi,
                'date' => $this->dateLabel($givenAt),
                'age' => $this->healthAgeLabel($birthDate, $givenAt),
                'timestamp' => $givenAt->getTimestamp(),
                'status' => $this->healthStatus($record->status),
                'recorder' => $record->kader?->nama ?? 'Kader posyandu',
                'recorderRole' => $this->healthRecorderRole($record->kader?->role),
            ];
        });

        return $immunizations
            ->concat($vitamins)
            ->sortByDesc('timestamp')
            ->values();
    }

    private function healthStatus(?string $status): string
    {
        if (blank($status)) {
            return 'Tercatat';
        }

        return str((string) $status)->replace('_', ' ')->title()->toString();
    }

    private function healthAgeLabel(Carbon $birthDate, Carbon $date): string
    {
        $difference = $birthDate->diff($date);
        $ageInMonths = ($difference->y * 12) + $difference->m;

        if ($difference->days === 0) {
            return '0 Hari (Saat Lahir)';
        }

        if ($ageInMonths === 0) {
            return 'Usia ' . $difference->days . ' Hari';
        }

        return 'Usia ' . $ageInMonths . ' Bulan';
    }

    private function healthRecorderRole(?string $role): string
    {
        return match ($role) {
            'bidan' => 'Bidan',
            'kader' => 'Kader Posyandu',
            default => 'Petugas Posyandu',
        };
    }

    private function statusTone(?string $status): string
    {
        $normalizedStatus = strtolower($status ?? '');

        return match (true) {
            str_contains($normalizedStatus, 'sangat pendek'),
            str_contains($normalizedStatus, 'sangat kurus') => 'danger',
            str_contains($normalizedStatus, 'pendek'),
            str_contains($normalizedStatus, 'kurus'),
            str_contains($normalizedStatus, 'kurang') => 'warning',
            $normalizedStatus === 'normal' => 'success',
            default => 'neutral',
        };
    }

    private function followUpLabel(string $tone, ?Verifikasi $verification): string
    {
        if ($tone === 'success') {
            return 'Tidak memerlukan tindak lanjut';
        }

        if ($verification?->tindak_lanjut === 'perlu') {
            return 'Perlu tindak lanjut sesuai arahan bidan';
        }

        if ($verification?->status === 'terverifikasi') {
            return 'Tidak memerlukan tindak lanjut';
        }

        return match ($tone) {
            'danger' => 'Perlu rujukan puskesmas',
            'warning' => 'Perlu pemantauan berkala',
            default => 'Pemantauan rutin',
        };
    }

    private function verificationLabel(?Verifikasi $verification): string
    {
        if ($verification === null) {
            return 'Belum diverifikasi';
        }

        $bidanName = $verification->bidan?->nama;

        return match ($verification->status) {
            'terverifikasi' => $bidanName
                ? 'Terverifikasi oleh: ' . $bidanName
                : 'Terverifikasi oleh bidan',
            default => 'Menunggu verifikasi bidan',
        };
    }

    private function verificationTone(?Verifikasi $verification): string
    {
        return match ($verification?->status) {
            'terverifikasi' => 'verified',
            'menunggu' => 'waiting',
            default => 'neutral',
        };
    }

    private function dateLabel(Carbon $date): string
    {
        return $date->locale('id')->translatedFormat('d F Y');
    }

    private function ageLabel(Carbon $birthDate, Carbon $date): string
    {
        $difference = $birthDate->diff($date);
        $parts = [];

        if ($difference->y > 0) {
            $parts[] = $difference->y . ' thn';
        }

        $parts[] = $difference->m . ' bln';

        return implode(' ', $parts);
    }

    private function measurementValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        return number_format((float) $value, 1, ',', '.');
    }

    private function signedValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        $numericValue = (float) $value;

        return ($numericValue > 0 ? '+' : '') . number_format($numericValue, 2, ',', '.');
    }
}
