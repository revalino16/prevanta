<?php

namespace App\Support;

use Carbon\CarbonInterface;
use InvalidArgumentException;

class HeightForAgeZScoreCalculator
{
    private const DAYS_PER_MONTH = 30.4375;

    private const LENGTH_MAXIMUM_AGE_IN_DAYS = 730;

    private const WEEKLY_REFERENCE_MAXIMUM_AGE_IN_DAYS = 91;

    /**
     * Weekly WHO LMS values preserve the faster growth curve during the first 13 weeks.
     *
     * @var array<string, array{median: list<float>, coefficient: list<float>}>
     */
    private const EARLY_LENGTH_REFERENCES = [
        'L' => [
            'median' => [49.8842, 51.1152, 52.3461, 53.3905, 54.3881, 55.3374, 56.2357, 57.0851, 57.8889, 58.6536, 59.3872, 60.0894, 60.7605, 61.4013],
            'coefficient' => [0.03795, 0.03723, 0.03652, 0.03609, 0.03570, 0.03534, 0.03501, 0.03470, 0.03442, 0.03416, 0.03392, 0.03369, 0.03348, 0.03329],
        ],
        'P' => [
            'median' => [49.1477, 50.3298, 51.5120, 52.4695, 53.3809, 54.2454, 55.0642, 55.8406, 56.5767, 57.2761, 57.9436, 58.5816, 59.1922, 59.7773],
            'coefficient' => [0.03790, 0.03742, 0.03694, 0.03669, 0.03647, 0.03627, 0.03609, 0.03593, 0.03578, 0.03564, 0.03552, 0.03540, 0.03530, 0.03520],
        ],
    ];

    /**
     * WHO Child Growth Standards LMS reference values for length-for-age (0-24 months).
     *
     * @var array<string, array{median: list<float>, coefficient: list<float>}>
     */
    private const LENGTH_REFERENCES = [
        'L' => [
            'median' => [49.8842, 54.6645, 58.4384, 61.4013, 63.9041, 65.8912, 67.6435, 69.1615, 70.6224, 71.9714, 73.2653, 74.5464, 75.7391, 76.9304, 78.0451, 79.1613, 80.2113, 81.2340, 82.2628, 83.2318, 84.2074, 85.1291, 86.0589, 86.9392, 87.8018],
            'coefficient' => [0.03795, 0.03559, 0.03423, 0.03329, 0.03257, 0.03204, 0.03165, 0.03139, 0.03124, 0.03117, 0.03118, 0.03125, 0.03137, 0.03154, 0.03174, 0.03197, 0.03222, 0.03249, 0.03279, 0.03310, 0.03342, 0.03375, 0.03410, 0.03445, 0.03479],
        ],
        'P' => [
            'median' => [49.1477, 53.6326, 57.0796, 59.7773, 62.1071, 64.0190, 65.7510, 67.2842, 68.7732, 70.1463, 71.4656, 72.7788, 74.0049, 75.2297, 76.3770, 77.5258, 78.6055, 79.6559, 80.7121, 81.7080, 82.7116, 83.6595, 84.6154, 85.5184, 86.4008],
            'coefficient' => [0.03790, 0.03641, 0.03568, 0.03520, 0.03486, 0.03463, 0.03448, 0.03441, 0.03440, 0.03444, 0.03452, 0.03464, 0.03479, 0.03496, 0.03514, 0.03534, 0.03555, 0.03576, 0.03598, 0.03620, 0.03643, 0.03665, 0.03689, 0.03711, 0.03733],
        ],
    ];

    /**
     * WHO Child Growth Standards LMS reference values for height-for-age (24-60 months).
     *
     * @var array<string, array{median: list<float>, coefficient: list<float>}>
     */
    private const HEIGHT_REFERENCES = [
        'L' => [
            'median' => [87.1303, 87.9737, 88.7964, 89.6247, 90.4056, 91.1906, 91.9297, 92.6735, 93.3753, 94.0612, 94.7559, 95.4168, 96.0889, 96.7298, 97.3827, 98.0060, 98.6412, 99.2471, 99.8441, 100.4522, 101.0326, 101.6246, 102.1910, 102.7706, 103.3273, 103.8806, 104.4496, 104.9984, 105.5641, 106.1104, 106.6736, 107.2176, 107.7607, 108.3209, 108.8621, 109.4203, 109.9593],
            'coefficient' => [0.03508, 0.03542, 0.03576, 0.03610, 0.03642, 0.03674, 0.03704, 0.03733, 0.03761, 0.03787, 0.03812, 0.03836, 0.03858, 0.03879, 0.03900, 0.03919, 0.03937, 0.03954, 0.03970, 0.03986, 0.04002, 0.04017, 0.04031, 0.04045, 0.04059, 0.04073, 0.04086, 0.04100, 0.04113, 0.04126, 0.04139, 0.04152, 0.04165, 0.04177, 0.04190, 0.04202, 0.04214],
        ],
        'P' => [
            'median' => [85.7299, 86.5922, 87.4358, 88.2881, 89.0938, 89.9072, 90.6765, 91.4539, 92.1906, 92.9135, 93.6473, 94.3460, 95.0572, 95.7356, 96.4270, 97.0871, 97.7601, 98.4028, 99.0369, 99.6834, 100.3007, 100.9301, 101.5312, 102.1446, 102.7312, 103.3113, 103.9045, 104.4727, 105.0541, 105.6114, 106.1817, 106.7284, 107.2698, 107.8238, 108.3547, 108.8981, 109.4189],
            'coefficient' => [0.03764, 0.03786, 0.03808, 0.03830, 0.03851, 0.03872, 0.03893, 0.03913, 0.03933, 0.03952, 0.03971, 0.03989, 0.04007, 0.04024, 0.04041, 0.04057, 0.04074, 0.04089, 0.04105, 0.04120, 0.04135, 0.04150, 0.04164, 0.04179, 0.04193, 0.04206, 0.04220, 0.04233, 0.04247, 0.04259, 0.04272, 0.04285, 0.04297, 0.04310, 0.04322, 0.04335, 0.04346],
        ],
    ];

    public function calculate(
        string $sex,
        CarbonInterface $birthDate,
        CarbonInterface $measurementDate,
        float $measuredLengthOrHeight,
        string $measurementPosition,
    ): float {
        if (! array_key_exists($sex, self::LENGTH_REFERENCES)) {
            throw new InvalidArgumentException('Jenis kelamin balita tidak didukung untuk perhitungan Z-score.');
        }

        if (! in_array($measurementPosition, ['terlentang', 'berdiri'], true)) {
            throw new InvalidArgumentException('Posisi pengukuran tidak dikenali.');
        }

        $ageInDays = (int) $birthDate->copy()->startOfDay()->diffInDays(
            $measurementDate->copy()->startOfDay(),
            false,
        );

        if ($ageInDays < 0) {
            throw new InvalidArgumentException('Tanggal pengukuran tidak boleh sebelum tanggal lahir.');
        }

        $usesLengthReference = $ageInDays <= self::LENGTH_MAXIMUM_AGE_IN_DAYS;
        $correctedMeasurement = $this->correctedMeasurement(
            $measuredLengthOrHeight,
            $measurementPosition,
            $usesLengthReference,
        );
        [$median, $coefficient] = $this->referenceValues($sex, $ageInDays, $usesLengthReference);

        return round(($correctedMeasurement - $median) / ($median * $coefficient), 2);
    }

    public function status(float $zScore): string
    {
        return match (true) {
            $zScore < -3 => 'sangat pendek',
            $zScore < -2 => 'pendek',
            $zScore <= 3 => 'normal',
            default => 'tinggi',
        };
    }

    private function correctedMeasurement(
        float $measurement,
        string $measurementPosition,
        bool $usesLengthReference,
    ): float {
        if ($usesLengthReference && $measurementPosition === 'berdiri') {
            return $measurement + 0.7;
        }

        if (! $usesLengthReference && $measurementPosition === 'terlentang') {
            return $measurement - 0.7;
        }

        return $measurement;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function referenceValues(string $sex, int $ageInDays, bool $usesLengthReference): array
    {
        if ($ageInDays <= self::WEEKLY_REFERENCE_MAXIMUM_AGE_IN_DAYS) {
            $reference = self::EARLY_LENGTH_REFERENCES[$sex];
            $age = $ageInDays / 7;
            $minimumAge = 0;
        } elseif ($usesLengthReference) {
            $reference = self::LENGTH_REFERENCES[$sex];
            $age = min(24.0, $ageInDays / self::DAYS_PER_MONTH);
            $minimumAge = 0;
        } else {
            $reference = self::HEIGHT_REFERENCES[$sex];
            $age = min(60.0, 24 + (($ageInDays - 731) / self::DAYS_PER_MONTH));
            $minimumAge = 24;
        }

        return [
            $this->interpolate($reference['median'], $age, $minimumAge),
            $this->interpolate($reference['coefficient'], $age, $minimumAge),
        ];
    }

    /**
     * @param  list<float>  $values
     */
    private function interpolate(array $values, float $age, int $minimumAge): float
    {
        $relativeAge = max(0.0, $age - $minimumAge);
        $lowerIndex = min((int) floor($relativeAge), count($values) - 1);
        $upperIndex = min($lowerIndex + 1, count($values) - 1);
        $fraction = $relativeAge - $lowerIndex;

        return $values[$lowerIndex] + (($values[$upperIndex] - $values[$lowerIndex]) * $fraction);
    }
}
