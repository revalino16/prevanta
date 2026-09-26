<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\ImunisasiBalita;
use App\Models\Pengukuran;
use App\Models\Verifikasi;
use App\Models\VitaminBalita;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class BalitaKmsController extends Controller
{
    private const CHART_LEFT = 68.0;

    private const CHART_RIGHT = 892.0;

    private const CHART_TOP = 28.0;

    private const CHART_BOTTOM = 318.0;

    public function __invoke(Balita $balita): View
    {
        $balita->load([
            'orangTua.user',
            'pengukuran' => fn ($query) => $query->oldest('tanggal_pengukuran'),
            'pengukuran.verifikasi.bidan',
            'imunisasi' => fn ($query) => $query->latest('tanggal_pemberian'),
            'imunisasi.jenisImunisasi',
            'imunisasi.kader',
            'vitamin' => fn ($query) => $query->latest('tanggal_pemberian'),
            'vitamin.jenisVitamin',
            'vitamin.kader',
        ]);

        $birthDate = Carbon::parse($balita->tanggal_lahir);
        $measurementHistory = $balita->pengukuran
            ->map(fn (Pengukuran $measurement): array => $this->measurementCard($measurement, $birthDate));

        return view('kader.profil-balita', [
            'balita' => $balita,
            'birthDateLabel' => $this->dateLabel($birthDate),
            'currentAgeLabel' => $this->ageLabel($birthDate, now('Asia/Jakarta')),
            'childCode' => 'BLT-'.str_pad((string) $balita->getKey(), 3, '0', STR_PAD_LEFT),
            'latestMeasurement' => $measurementHistory->last(),
            'measurementHistory' => $measurementHistory->reverse()->values(),
            'growthCharts' => $this->growthCharts($balita->pengukuran, $birthDate),
            'healthHistory' => $this->healthHistory($balita),
            'healthSummary' => [
                'immunizations' => $balita->imunisasi->count(),
                'vitamins' => $balita->vitamin->count(),
            ],
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     date: string,
     *     age: string,
     *     height: string,
     *     weight: string,
     *     armCircumference: string,
     *     headCircumference: string,
     *     zScore: string,
     *     status: string,
     *     tone: string,
     *     followUp: string,
     *     verificationLabel: string,
     *     verificationTone: string
     * }
     */
    private function measurementCard(Pengukuran $measurement, Carbon $birthDate): array
    {
        $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
        $tone = $this->statusTone($measurement->status_pertumbuhan);
        $verification = $measurement->verifikasi;

        return [
            'id' => (int) $measurement->getKey(),
            'date' => $this->dateLabel($measurementDate),
            'age' => $this->ageLabel($birthDate, $measurementDate),
            'height' => $this->measurementValue($measurement->tinggi_badan),
            'weight' => $this->measurementValue($measurement->berat_badan),
            'armCircumference' => $this->measurementValue($measurement->lingkar_lengan_atas),
            'headCircumference' => $this->measurementValue($measurement->lingkar_kepala),
            'zScore' => $this->signedValue($measurement->z_score),
            'status' => $measurement->status_pertumbuhan ?: 'Belum diklasifikasikan',
            'tone' => $tone,
            'followUp' => $this->followUpLabel($tone, $verification),
            'verificationLabel' => $this->verificationLabel($verification),
            'verificationTone' => $this->verificationTone($verification),
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, array<string, mixed>>
     */
    private function growthCharts(Collection $measurements, Carbon $birthDate): array
    {
        return [
            'tb-u' => $this->heightForAgeChart($measurements, $birthDate),
            'bb-u' => $this->weightForAgeChart($measurements, $birthDate),
            'bb-tb' => $this->weightForHeightChart($measurements),
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, mixed>
     */
    private function heightForAgeChart(Collection $measurements, Carbon $birthDate): array
    {
        $chartMeasurements = $measurements
            ->filter(fn (Pengukuran $measurement): bool => $measurement->z_score !== null)
            ->map(function (Pengukuran $measurement) use ($birthDate): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
                $age = max(0, (int) $birthDate->diffInMonths($measurementDate));

                return [
                    'xValue' => (float) $age,
                    'yValue' => (float) $measurement->z_score,
                    'tooltip' => $this->dateLabel($measurementDate)
                        .' — usia '.$age.' bulan — '.$this->signedValue($measurement->z_score).' SD',
                ];
            })
            ->values();

        $maxAge = $this->maximumAge($chartMeasurements->pluck('xValue'));
        $points = $this->plotPoints($chartMeasurements, 0, $maxAge, -4, 3);

        return [
            'label' => 'TB/U',
            'subLabel' => 'Tinggi/Umur',
            'description' => 'Z-Score tinggi badan menurut usia berdasarkan data pengukuran.',
            'points' => $points,
            'polyline' => $this->polyline($points),
            'xTicks' => $this->ageTicks($maxAge),
            'yTicks' => $this->zScoreTicks(),
            'showsZones' => true,
            'emptyTitle' => 'Belum ada data Z-Score TB/U',
            'emptyCopy' => 'Grafik akan terisi setelah Z-Score pengukuran dicatat.',
            'legends' => [
                ['tone' => 'line', 'label' => 'Pertumbuhan anak'],
                ['tone' => 'normal', 'label' => 'Zona Hijau (Normal: -2 s/d +2 SD)'],
                ['tone' => 'warning', 'label' => 'Zona Kuning (Pendek: -3 s/d -2 SD)'],
                ['tone' => 'danger', 'label' => 'Zona Merah (Sangat Pendek: < -3 SD)'],
            ],
            'note' => 'Interpretasi TB/U mengikuti Z-Score yang dicatat pada setiap pemeriksaan.',
        ];
    }

    /**
     * @param  Collection<int, Pengukuran>  $measurements
     * @return array<string, mixed>
     */
    private function weightForAgeChart(Collection $measurements, Carbon $birthDate): array
    {
        $chartMeasurements = $measurements
            ->filter(fn (Pengukuran $measurement): bool => $measurement->berat_badan !== null)
            ->map(function (Pengukuran $measurement) use ($birthDate): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);
                $age = max(0, (int) $birthDate->diffInMonths($measurementDate));

                return [
                    'xValue' => (float) $age,
                    'yValue' => (float) $measurement->berat_badan,
                    'tooltip' => $this->dateLabel($measurementDate)
                        .' — usia '.$age.' bulan — BB '.$this->measurementValue($measurement->berat_badan).' kg',
                ];
            })
            ->values();

        $maxAge = $this->maximumAge($chartMeasurements->pluck('xValue'));
        [$minimumWeight, $maximumWeight] = $this->valueBounds($chartMeasurements->pluck('yValue'), 2, 0);
        $points = $this->plotPoints($chartMeasurements, 0, $maxAge, $minimumWeight, $maximumWeight);

        return [
            'label' => 'BB/U',
            'subLabel' => 'Berat/Umur',
            'description' => 'Tren berat badan aktual menurut usia pada setiap pemeriksaan.',
            'points' => $points,
            'polyline' => $this->polyline($points),
            'xTicks' => $this->ageTicks($maxAge),
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
            ->filter(fn (Pengukuran $measurement): bool => $measurement->berat_badan !== null
                && $measurement->tinggi_badan !== null)
            ->map(function (Pengukuran $measurement): array {
                $measurementDate = Carbon::parse($measurement->tanggal_pengukuran);

                return [
                    'xValue' => (float) $measurement->tinggi_badan,
                    'yValue' => (float) $measurement->berat_badan,
                    'tooltip' => $this->dateLabel($measurementDate)
                        .' — TB '.$this->measurementValue($measurement->tinggi_badan).' cm'
                        .' — BB '.$this->measurementValue($measurement->berat_badan).' kg',
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
            'emptyTitle' => 'Belum ada data berat dan tinggi badan',
            'emptyCopy' => 'Grafik BB/TB akan terisi setelah kedua nilai dicatat.',
            'legends' => [
                ['tone' => 'line', 'label' => 'Perbandingan berat dan tinggi anak'],
                ['tone' => 'recorded', 'label' => 'Data pengukuran tersimpan'],
            ],
            'note' => 'Grafik BB/TB menampilkan data aktual. Klasifikasi gizi memerlukan Z-Score BB/TB tersendiri.',
        ];
    }

    /**
     * @param  Collection<int, array{xValue: float, yValue: float, tooltip: string}>  $measurements
     * @return array<int, array{x: float, y: float, tooltip: string}>
     */
    private function plotPoints(
        Collection $measurements,
        float $minimumX,
        float $maximumX,
        float $minimumY,
        float $maximumY,
    ): array {
        return $measurements
            ->map(fn (array $measurement): array => [
                'x' => round($this->scale(
                    $measurement['xValue'],
                    $minimumX,
                    $maximumX,
                    self::CHART_LEFT,
                    self::CHART_RIGHT,
                ), 2),
                'y' => round($this->scale(
                    $measurement['yValue'],
                    $minimumY,
                    $maximumY,
                    self::CHART_BOTTOM,
                    self::CHART_TOP,
                ), 2),
                'tooltip' => $measurement['tooltip'],
            ])
            ->all();
    }

    /**
     * @param  array<int, array{x: float, y: float, tooltip: string}>  $points
     */
    private function polyline(array $points): string
    {
        return collect($points)
            ->map(fn (array $point): string => $point['x'].','.$point['y'])
            ->implode(' ');
    }

    /**
     * @param  Collection<int, float|int>  $ages
     */
    private function maximumAge(Collection $ages): float
    {
        $oldestAge = (int) ($ages->max() ?? 0);

        return (float) max(6, (int) (ceil($oldestAge / 6) * 6));
    }

    /**
     * @return array<int, array{x: float, label: string}>
     */
    private function ageTicks(float $maximumAge): array
    {
        return collect(range(0, 5))
            ->map(function (int $index) use ($maximumAge): array {
                $age = (int) round(($maximumAge / 5) * $index);

                return [
                    'x' => round(self::CHART_LEFT + (($index / 5) * (self::CHART_RIGHT - self::CHART_LEFT)), 2),
                    'label' => $age === 0 ? 'Lahir' : $age.' bln',
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{y: float, label: string, tone: string}>
     */
    private function zScoreTicks(): array
    {
        return collect([3, 2, 0, -2, -3, -4])
            ->map(fn (int $score): array => [
                'y' => round($this->scale($score, -4, 3, self::CHART_BOTTOM, self::CHART_TOP), 2),
                'label' => match ($score) {
                    0 => '0 (Median)',
                    default => ($score > 0 ? '+' : '').$score.' SD',
                },
                'tone' => match (true) {
                    $score >= 0 => 'green',
                    $score === -2 => 'amber',
                    default => 'red',
                },
            ])
            ->all();
    }

    /**
     * @param  Collection<int, float|int>  $values
     * @return array{0: float, 1: float}
     */
    private function valueBounds(Collection $values, float $minimumSpan, float $floor): array
    {
        if ($values->isEmpty()) {
            return [$floor, $floor + $minimumSpan];
        }

        $minimum = (float) $values->min();
        $maximum = (float) $values->max();
        $span = max($minimumSpan, $maximum - $minimum);
        $padding = max($span * .15, $minimumSpan / 2);
        $lower = max($floor, floor(($minimum - $padding) * 2) / 2);
        $upper = ceil(($maximum + $padding) * 2) / 2;

        if (($upper - $lower) < $minimumSpan) {
            $upper = $lower + $minimumSpan;
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
                    'label' => $this->axisValue($value).' '.$unit,
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
                    'label' => $this->axisValue($value).' '.$unit,
                ];
            })
            ->all();
    }

    private function scale(float $value, float $minimum, float $maximum, float $start, float $end): float
    {
        if ($maximum === $minimum) {
            return ($start + $end) / 2;
        }

        return $start + ((($value - $minimum) / ($maximum - $minimum)) * ($end - $start));
    }

    private function axisValue(float $value): string
    {
        return number_format($value, $value === floor($value) ? 0 : 1, ',', '.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function healthHistory(Balita $balita): Collection
    {
        $immunizations = $balita->imunisasi->map(fn (ImunisasiBalita $record): array => [
            'id' => (int) $record->getKey(),
            'type' => 'Imunisasi',
            'tone' => 'immunization',
            'icon' => 'fa-syringe',
            'name' => $record->jenisImunisasi?->nama_imunisasi ?? 'Imunisasi',
            'description' => $record->jenisImunisasi?->deskripsi,
            'date' => $this->dateLabel(Carbon::parse($record->tanggal_pemberian)),
            'timestamp' => Carbon::parse($record->tanggal_pemberian)->getTimestamp(),
            'status' => $this->healthStatus($record->status),
            'recorder' => $record->kader?->nama ?? 'Kader posyandu',
        ]);

        $vitamins = $balita->vitamin->map(fn (VitaminBalita $record): array => [
            'id' => (int) $record->getKey(),
            'type' => 'Vitamin',
            'tone' => 'vitamin',
            'icon' => 'fa-capsules',
            'name' => $record->jenisVitamin?->nama_vitamin ?? 'Vitamin',
            'description' => $record->jenisVitamin?->deskripsi,
            'date' => $this->dateLabel(Carbon::parse($record->tanggal_pemberian)),
            'timestamp' => Carbon::parse($record->tanggal_pemberian)->getTimestamp(),
            'status' => $this->healthStatus($record->status),
            'recorder' => $record->kader?->nama ?? 'Kader posyandu',
        ]);

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
            return $verification->catatan_penyuluhan ?: 'Perlu tindak lanjut sesuai arahan bidan';
        }

        if ($verification?->status === 'terverifikasi') {
            return 'Tidak memerlukan tindak lanjut';
        }

        if ($verification?->status === 'dikembalikan') {
            return $verification->catatan_penyuluhan ?: 'Data pengukuran perlu diperbaiki';
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
                ? 'Terverifikasi oleh: '.$bidanName
                : 'Terverifikasi oleh bidan',
            'dikembalikan' => $bidanName
                ? 'Dikembalikan oleh: '.$bidanName
                : 'Dikembalikan oleh bidan',
            default => 'Menunggu verifikasi bidan',
        };
    }

    private function verificationTone(?Verifikasi $verification): string
    {
        return match ($verification?->status) {
            'terverifikasi' => 'verified',
            'dikembalikan' => 'returned',
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
            $parts[] = $difference->y.' thn';
        }

        $parts[] = $difference->m.' bln';

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

        return ($numericValue > 0 ? '+' : '').number_format($numericValue, 2, ',', '.');
    }
}
