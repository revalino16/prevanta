<?php

namespace Tests\Unit\Support;

use App\Support\HeightForAgeZScoreCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HeightForAgeZScoreCalculatorTest extends TestCase
{
    public function test_calculates_a_zero_z_score_at_the_who_median_for_a_newborn_boy(): void
    {
        $calculator = new HeightForAgeZScoreCalculator;

        $zScore = $calculator->calculate(
            'L',
            CarbonImmutable::parse('2026-01-01'),
            CarbonImmutable::parse('2026-01-01'),
            49.8842,
            'terlentang',
        );

        $this->assertSame(0.0, $zScore);
    }

    public function test_corrects_standing_height_for_a_child_younger_than_two_years(): void
    {
        $calculator = new HeightForAgeZScoreCalculator;

        $zScore = $calculator->calculate(
            'L',
            CarbonImmutable::parse('2026-01-01'),
            CarbonImmutable::parse('2026-01-01'),
            49.1842,
            'berdiri',
        );

        $this->assertSame(0.0, $zScore);
    }

    public function test_corrects_recumbent_length_for_a_child_older_than_two_years(): void
    {
        $calculator = new HeightForAgeZScoreCalculator;

        $zScore = $calculator->calculate(
            'L',
            CarbonImmutable::parse('2024-01-01'),
            CarbonImmutable::parse('2026-01-02'),
            87.8587,
            'terlentang',
        );

        $this->assertSame(0.0, $zScore);
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function growthStatuses(): array
    {
        return [
            'sangat pendek' => [-3.01, 'sangat pendek'],
            'batas pendek bawah' => [-3.0, 'pendek'],
            'pendek' => [-2.01, 'pendek'],
            'batas normal bawah' => [-2.0, 'normal'],
            'batas normal atas' => [3.0, 'normal'],
            'di atas normal' => [3.01, 'normal'],
        ];
    }

    #[DataProvider('growthStatuses')]
    public function test_classifies_height_for_age_status(float $zScore, string $expectedStatus): void
    {
        $calculator = new HeightForAgeZScoreCalculator;

        $status = $calculator->status($zScore);

        $this->assertSame($expectedStatus, $status);
    }
}
