<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\ImunisasiBalita;
use App\Models\Pengukuran;
use App\Models\Verifikasi;
use App\Models\VitaminBalita;
use App\Support\BalitaGrowthChartBuilder;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class BalitaKmsController extends Controller
{
    public function __invoke(Balita $balita, BalitaGrowthChartBuilder $growthChartBuilder): View
    {
        $balita->load([
            'pengukuran' => fn ($query) => $query->oldest('tanggal_pengukuran'),
            'pengukuran.kader',
            'pengukuran.verifikasi',
            'pengukuran.verifikasi.bidan',
            'imunisasi' => fn ($query) => $query->latest('tanggal_pemberian'),
            'imunisasi.jenisImunisasi',
            'imunisasi.kader',
            'vitamin' => fn ($query) => $query->latest('tanggal_pemberian'),
            'vitamin.jenisVitamin',
            'vitamin.kader',
        ]);

        $birthDate = Carbon::parse($balita->tanggal_lahir);

        $verifiedMeasurements = $balita->pengukuran
            ->filter(fn (Pengukuran $m) => $m->verifikasi?->status === 'terverifikasi')
            ->values();

        $measurementHistory = $verifiedMeasurements
            ->map(fn (Pengukuran $measurement): array => $this->measurementCard($measurement, $birthDate));

        $healthHistory = $this->healthHistory($balita, $birthDate);

        $view = request()->user()?->role === 'orang_tua'
            ? 'orangtua.profil-balita'
            : 'kader.profil-balita';

        return view($view, [
            'balita' => $balita,
            'birthDateLabel' => $this->dateLabel($birthDate),
            'currentAgeLabel' => $this->ageLabel($birthDate, now('Asia/Jakarta')),
            'childCode' => 'BLT-'.str_pad((string) $balita->getKey(), 3, '0', STR_PAD_LEFT),
            'latestMeasurement' => $measurementHistory->last(),
            'measurementHistory' => $measurementHistory->reverse()->values(),
            'growthCharts' => $growthChartBuilder->build($verifiedMeasurements, $birthDate, (string) ($balita->jenis_kelamin ?? 'L')),
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
            return 'Usia '.$difference->days.' Hari';
        }

        return 'Usia '.$ageInMonths.' Bulan';
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
                ? 'Terverifikasi oleh: '.$bidanName
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
