<?php

namespace App\Support;

use RuntimeException;

class WhoWeightGrowthReferences
{
    private const DATA_FILE = 'data/who_weight_growth_references.json';

    /** @var array<string, mixed>|null */
    private static ?array $tables = null;

    /**
     * Return WHO weight-for-age points as [month, -3 SD, -2 SD, median, +2 SD, +3 SD].
     *
     * @return list<array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}>
     */
    public function weightForAge(string $sex): array
    {
        return $this->table('wfa', $sex);
    }

    /**
     * Return the WHO combined weight-for-length/height curve points in centimetres.
     * The source switches from weight-for-length to weight-for-height at 86.5 cm.
     *
     * @return list<array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}>
     */
    public function weightForLengthHeight(string $sex): array
    {
        return $this->table('wflh', $sex);
    }

    /**
     * @return list<array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}>
     */
    private function table(string $indicator, string $sex): array
    {
        $sex = strtoupper($sex) === 'P' ? 'P' : 'L';
        $rows = self::loadTables()[$indicator][$sex] ?? null;

        if (! is_array($rows)) {
            throw new RuntimeException("Referensi WHO untuk indikator {$indicator} tidak tersedia.");
        }

        return $rows;
    }

    /** @return array<string, mixed> */
    private static function loadTables(): array
    {
        if (self::$tables !== null) {
            return self::$tables;
        }

        $path = resource_path(self::DATA_FILE);
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Tabel referensi pertumbuhan WHO tidak dapat dibaca.');
        }

        $tables = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($tables)) {
            throw new RuntimeException('Format tabel referensi pertumbuhan WHO tidak valid.');
        }

        self::$tables = $tables;

        return self::$tables;
    }
}
