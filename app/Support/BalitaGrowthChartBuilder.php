<?php

namespace App\Support;

use App\Models\Pengukuran;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BalitaGrowthChartBuilder
{
    private const CHART_LEFT = 68;

    private const CHART_RIGHT = 860;

    private const CHART_TOP = 28;

    private const CHART_BOTTOM = 318;

    public function __construct(private readonly WhoWeightGrowthReferences $weightReferences) {}

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, array<string, mixed>>
     */
    public function build(Collection $measurements, Carbon $birthDate, string $gender = 'L'): array
    {
        return [
            'tb-u' => $this->heightForAgeChart($measurements, $birthDate, $gender),
            'bb-u' => $this->weightForAgeChart($measurements, $birthDate, $gender),
            'bb-tb' => $this->weightForHeightChart($measurements, $birthDate, $gender),
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, mixed>
     */
    private function heightForAgeChart(Collection $measurements, Carbon $birthDate, string $gender = 'L'): array
    {
        $chartMeasurements = $measurements
            ->filter(fn (Pengukuran $measurement): bool => $measurement->tinggi_badan !== null)
            ->map(function (Pengukuran $measurement) use ($birthDate): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
                $age = max(0, (int) $birthDate->diffInMonths($measurementDate));
                $tb = (float) $measurement->tinggi_badan;

                return [
                    'xValue' => (float) $age,
                    'yValue' => $tb,
                    'tooltip' => $this->dateLabel($measurementDate)
                        .' — Usia: '.$age.' Bulan — TB: '.$this->measurementValue($tb).' cm'
                        .($measurement->z_score !== null ? ' (Z-Score: '.$this->signedValue($measurement->z_score).' SD)' : ''),
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
                $label = $m === 0 ? '0' : ($m % 12 === 0 ? ($m / 12).' thn ('.$m.')' : (string) $m);
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
    private function weightForAgeChart(Collection $measurements, Carbon $birthDate, string $gender): array
    {
        $chartMeasurements = $measurements
            ->filter(fn (Pengukuran $measurement): bool => $measurement->berat_badan !== null)
            ->map(function (Pengukuran $measurement) use ($birthDate): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
                $age = max(0, (int) $birthDate->diffInMonths($measurementDate));
                $weight = (float) $measurement->berat_badan;

                return [
                    'xValue' => (float) min(60, $age),
                    'yValue' => $weight,
                    'tooltip' => $this->dateLabel($measurementDate)
                        .' — usia '.$age.' bulan — BB '.$this->measurementValue($weight).' kg',
                ];
            })
            ->sortBy('xValue')
            ->values();

        $minimumAge = 0.0;
        $maximumAge = 60.0;
        $minimumWeight = 2.0;
        $maximumWeight = 28.0;
        $points = $this->plotPoints($chartMeasurements, $minimumAge, $maximumAge, $minimumWeight, $maximumWeight);
        $layers = $this->weightReferenceLayers(
            $this->weightReferences->weightForAge($gender),
            $minimumAge,
            $maximumAge,
            $minimumWeight,
            $maximumWeight,
        );
        $genderUpper = strtoupper($gender) === 'P' ? 'P' : 'L';
        $genderLabel = $genderUpper === 'P' ? 'Anak Perempuan' : 'Anak Laki-Laki';

        return [
            'label' => 'BB/U',
            'subLabel' => 'Berat/Umur',
            'chartTitle' => 'Berat Badan Menurut Umur (0 - 5 Tahun)',
            'standardTag' => 'Standar Pertumbuhan Anak (WHO Growth Standards)',
            'gender' => $genderUpper,
            'genderLabel' => $genderLabel,
            'description' => 'Grafik berat badan menurut umur dengan kurva baku WHO sesuai jenis kelamin.',
            'xAxisTitle' => 'Umur (Bulan dan Tahun)',
            'yAxisTitle' => 'Berat Badan (kg)',
            'points' => $points,
            'polyline' => $this->polyline($points),
            'xTicks' => $this->ageTicks($maximumAge, 6),
            'xMinorGrid' => $this->ageMinorGrid($maximumAge),
            'yTicks' => $this->weightTicks($minimumWeight, $maximumWeight, 2),
            'yMinorGrid' => $this->weightMinorGrid($minimumWeight, $maximumWeight, 2),
            'chartBounds' => $this->chartBounds(),
            'showsZones' => true,
            'curvedZones' => $layers['curvedZones'],
            'whoCurves' => $layers['whoCurves'],
            'emptyTitle' => 'Belum ada data berat badan',
            'emptyCopy' => 'Grafik BB/U akan terisi setelah berat badan dicatat.',
            'legends' => [
                ['tone' => 'line', 'label' => 'Berat badan anak'],
                ['tone' => 'normal', 'label' => 'Normal (-2 s/d +2 SD)'],
                ['tone' => 'warning', 'label' => 'Berat badan kurang (-3 s/d -2 SD)'],
                ['tone' => 'danger', 'label' => 'Berat badan sangat kurang (< -3 SD)'],
            ],
            'note' => 'Kurva dan batas SD memakai tabel baku WHO BB/U untuk anak laki-laki atau perempuan.',
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, mixed>
     */
    private function weightForHeightChart(Collection $measurements, Carbon $birthDate, string $gender): array
    {
        $chartMeasurements = $measurements
            ->filter(fn (Pengukuran $measurement): bool => $measurement->berat_badan !== null
                && $measurement->tinggi_badan !== null)
            ->map(function (Pengukuran $measurement) use ($birthDate): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
                $ageInDays = (int) $birthDate->copy()->startOfDay()->diffInDays($measurementDate->copy()->startOfDay());
                $usesLengthReference = $ageInDays <= 730;
                $measurementPosition = $measurement->posisi_pengukuran
                    ?? ($usesLengthReference ? 'terlentang' : 'berdiri');
                $lengthOrHeight = (float) $measurement->tinggi_badan;
                $lengthOrHeight = $this->correctLengthOrHeight(
                    $lengthOrHeight,
                    $measurementPosition,
                    $usesLengthReference,
                );

                return [
                    'xValue' => $lengthOrHeight,
                    'yValue' => (float) $measurement->berat_badan,
                    'tooltip' => $this->dateLabel($measurementDate)
                        .' — PB/TB '.$this->measurementValue($measurement->tinggi_badan).' cm'
                        .' — BB '.$this->measurementValue($measurement->berat_badan).' kg',
                ];
            })
            ->sortBy('xValue')
            ->values();

        $minimumHeight = 45.0;
        $maximumHeight = 120.0;
        $minimumWeight = 2.0;
        $maximumWeight = 34.0;
        $points = $this->plotPoints(
            $chartMeasurements,
            $minimumHeight,
            $maximumHeight,
            $minimumWeight,
            $maximumWeight,
        );
        $layers = $this->weightReferenceLayers(
            $this->weightReferences->weightForLengthHeight($gender),
            $minimumHeight,
            $maximumHeight,
            $minimumWeight,
            $maximumWeight,
            classifyHighWeight: true,
        );
        $genderUpper = strtoupper($gender) === 'P' ? 'P' : 'L';
        $genderLabel = $genderUpper === 'P' ? 'Anak Perempuan' : 'Anak Laki-Laki';

        return [
            'label' => 'BB/TB',
            'subLabel' => 'Berat/Panjang-Tinggi',
            'chartTitle' => 'Berat Badan Menurut Panjang/Tinggi Badan (0 - 5 Tahun)',
            'standardTag' => 'Standar Pertumbuhan Anak (WHO Growth Standards)',
            'gender' => $genderUpper,
            'genderLabel' => $genderLabel,
            'description' => 'Grafik berat badan menurut panjang atau tinggi badan dengan kurva baku WHO sesuai jenis kelamin.',
            'xAxisTitle' => 'Panjang/Tinggi Badan (cm)',
            'yAxisTitle' => 'Berat Badan (kg)',
            'points' => $points,
            'polyline' => $this->polyline($points),
            'xTicks' => $this->horizontalValueTicks($minimumHeight, $maximumHeight, 'cm', 5),
            'xMinorGrid' => $this->heightMinorGrid($minimumHeight, $maximumHeight),
            'yTicks' => $this->weightTicks($minimumWeight, $maximumWeight, 2),
            'yMinorGrid' => $this->weightMinorGrid($minimumWeight, $maximumWeight, 2),
            'chartBounds' => $this->chartBounds(),
            'showsZones' => true,
            'curvedZones' => $layers['curvedZones'],
            'whoCurves' => $layers['whoCurves'],
            'emptyTitle' => 'Belum ada data perbandingan',
            'emptyCopy' => 'Grafik BB/TB akan terisi setelah berat & tinggi dicatat.',
            'legends' => [
                ['tone' => 'line', 'label' => 'Perbandingan berat dan tinggi anak'],
                ['tone' => 'normal', 'label' => 'Rentang normal (-2 s/d +2 SD)'],
                ['tone' => 'warning', 'label' => 'Kurus (-3 s/d -2 SD) atau berat berlebih (+2 s/d +3 SD)'],
                ['tone' => 'danger', 'label' => 'Sangat kurus (< -3 SD)'],
                ['tone' => 'obese', 'label' => 'Obesitas (> +3 SD)'],
            ],
            'note' => 'Standar WHO memakai BB/PB hingga usia 2 tahun dan BB/TB setelahnya. Posisi ukur dikoreksi 0,7 cm bila tidak sesuai usia.',
        ];
    }

    /**
     * @param  list<array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}>  $references
     * @return array{whoCurves: array<int, array<string, mixed>>, curvedZones: array<int, array{type: string, points: string}>}
     */
    private function weightReferenceLayers(
        array $references,
        float $minimumX,
        float $maximumX,
        float $minimumY,
        float $maximumY,
        bool $classifyHighWeight = false,
    ): array {
        $series = ['+3' => [], '+2' => [], '0' => [], '-2' => [], '-3' => []];
        $referenceIndexes = ['+3' => 5, '+2' => 4, '0' => 3, '-2' => 2, '-3' => 1];

        foreach ($references as $row) {
            foreach ($referenceIndexes as $label => $valueIndex) {
                $series[$label][] = [
                    'x' => $this->scale($row[0], $minimumX, $maximumX, self::CHART_LEFT, self::CHART_RIGHT),
                    'y' => $this->scale($row[$valueIndex], $minimumY, $maximumY, self::CHART_BOTTOM, self::CHART_TOP),
                ];
            }
        }

        $curveStyles = [
            '+3' => ['label' => '+3 SD', 'short' => '3', 'tone' => 'neutral'],
            '+2' => ['label' => '+2 SD', 'short' => '2', 'tone' => 'green'],
            '0' => ['label' => '0 SD (Median Standar WHO)', 'short' => '0', 'tone' => 'median'],
            '-2' => ['label' => '-2 SD', 'short' => '-2', 'tone' => 'amber'],
            '-3' => ['label' => '-3 SD', 'short' => '-3', 'tone' => 'red'],
        ];
        $whoCurves = [];

        foreach ($curveStyles as $key => $style) {
            $whoCurves[] = [
                ...$style,
                'points' => $this->polyline($series[$key]),
                'end' => end($series[$key]),
            ];
        }

        $bottom = [
            ['x' => self::CHART_RIGHT, 'y' => self::CHART_BOTTOM],
            ['x' => self::CHART_LEFT, 'y' => self::CHART_BOTTOM],
        ];
        $curvedZones = [
            ['type' => 'normal', 'points' => $this->polyline(array_merge($series['+2'], array_reverse($series['-2'])))],
            ['type' => 'warning', 'points' => $this->polyline(array_merge($series['-2'], array_reverse($series['-3'])))],
            ['type' => 'danger', 'points' => $this->polyline(array_merge($series['-3'], $bottom))],
        ];

        if ($classifyHighWeight) {
            $top = [
                ['x' => self::CHART_RIGHT, 'y' => self::CHART_TOP],
                ['x' => self::CHART_LEFT, 'y' => self::CHART_TOP],
            ];
            $curvedZones[] = ['type' => 'overweight', 'points' => $this->polyline(array_merge($series['+2'], array_reverse($series['+3'])))];
            $curvedZones[] = ['type' => 'obese', 'points' => $this->polyline(array_merge($series['+3'], $top))];
        }

        return ['whoCurves' => $whoCurves, 'curvedZones' => $curvedZones];
    }

    private function correctLengthOrHeight(float $value, string $position, bool $usesLengthReference): float
    {
        if ($usesLengthReference && $position === 'berdiri') {
            return $value + 0.7;
        }

        if (! $usesLengthReference && $position === 'terlentang') {
            return $value - 0.7;
        }

        return $value;
    }

    /** @return array{left: int, right: int, top: int, bottom: int} */
    private function chartBounds(): array
    {
        return [
            'left' => self::CHART_LEFT,
            'right' => self::CHART_RIGHT,
            'top' => self::CHART_TOP,
            'bottom' => self::CHART_BOTTOM,
        ];
    }

    /** @return array<int, array{x: float, isYear: bool, isStep: bool, month: int}> */
    private function ageMinorGrid(float $maximumAge): array
    {
        return collect(range(0, (int) $maximumAge))
            ->map(fn (int $month): array => [
                'x' => $this->scale((float) $month, 0, $maximumAge, self::CHART_LEFT, self::CHART_RIGHT),
                'isYear' => $month % 12 === 0,
                'isStep' => $month % 6 === 0,
                'month' => $month,
            ])
            ->all();
    }

    /** @return array<int, array{x: float, isYear: bool, isStep: bool, month: int}> */
    private function heightMinorGrid(float $minimumHeight, float $maximumHeight): array
    {
        $grid = [];

        for ($centimeter = (int) $minimumHeight; $centimeter <= $maximumHeight; $centimeter++) {
            $grid[] = [
                'x' => $this->scale((float) $centimeter, $minimumHeight, $maximumHeight, self::CHART_LEFT, self::CHART_RIGHT),
                'isYear' => false,
                'isStep' => $centimeter % 5 === 0,
                'month' => $centimeter,
            ];
        }

        return $grid;
    }

    /** @return array<int, array{y: float, isMajor: bool, val: int}> */
    private function weightMinorGrid(float $minimumWeight, float $maximumWeight, int $majorStep): array
    {
        $grid = [];

        for ($kilogram = (int) $minimumWeight; $kilogram <= $maximumWeight; $kilogram++) {
            $grid[] = [
                'y' => $this->scale((float) $kilogram, $minimumWeight, $maximumWeight, self::CHART_BOTTOM, self::CHART_TOP),
                'isMajor' => $kilogram % $majorStep === 0,
                'val' => $kilogram,
            ];
        }

        return $grid;
    }

    /** @return array<int, array{y: float, label: string, tone: string}> */
    private function weightTicks(float $minimumWeight, float $maximumWeight, int $step): array
    {
        $ticks = [];

        for ($weight = $minimumWeight; $weight <= $maximumWeight; $weight += $step) {
            $ticks[] = [
                'y' => $this->scale($weight, $minimumWeight, $maximumWeight, self::CHART_BOTTOM, self::CHART_TOP),
                'label' => (string) (int) $weight,
                'tone' => 'neutral',
            ];
        }

        return array_reverse($ticks);
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
            ->map(fn (array $point): string => "{$point['x']},{$point['y']}")
            ->join(' ');
    }

    /**
     * @return array<int, array{x: float, label: string}>
     */
    private function ageTicks(float $maxAge, float $step): array
    {
        $ticks = [];

        for ($age = 0; $age <= $maxAge; $age += $step) {
            $label = $age === 0 ? '0 bln' : ($age % 12 === 0 ? ($age / 12).' thn' : $age.' bln');
            $ticks[] = [
                'x' => $this->scale($age, 0, $maxAge, self::CHART_LEFT, self::CHART_RIGHT),
                'label' => $label,
            ];
        }

        return $ticks;
    }

    /**
     * @return array<int, array{x: float, label: string}>
     */
    private function horizontalValueTicks(float $minimum, float $maximum, string $unit, float $step = 1): array
    {
        $ticks = [];

        for ($value = $minimum; $value <= $maximum; $value += $step) {
            $ticks[] = [
                'x' => $this->scale($value, $minimum, $maximum, self::CHART_LEFT, self::CHART_RIGHT),
                'label' => trim($this->axisValue($value).' '.$unit),
            ];
        }

        return $ticks;
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

    private function dateLabel(Carbon $date): string
    {
        return $date->locale('id')->translatedFormat('d F Y');
    }

    private function measurementValue(mixed $value): string
    {
        return $value === null ? '—' : number_format((float) $value, 1, ',', '.');
    }

    private function signedValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        $numericValue = (float) $value;

        return ($numericValue > 0 ? '+' : '').number_format($numericValue, 2, ',', '.');
    }
}
