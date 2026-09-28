<?php

namespace App\Support;

final class AnthropometricMeasurementLimits
{
    public const float WEIGHT_MIN_KG = 0.9;

    public const float WEIGHT_MAX_KG = 30.0;

    public const float HEIGHT_MIN_CM = 35.0;

    public const float HEIGHT_MAX_CM = 130.0;

    public const float ARM_CIRCUMFERENCE_MIN_CM = 5.0;

    public const float ARM_CIRCUMFERENCE_MAX_CM = 25.0;

    public const float HEAD_CIRCUMFERENCE_MIN_CM = 20.0;

    public const float HEAD_CIRCUMFERENCE_MAX_CM = 60.0;

    /**
     * @return array<string, array{min: float, max: float}>
     */
    public static function all(): array
    {
        return [
            'berat_badan' => [
                'min' => self::WEIGHT_MIN_KG,
                'max' => self::WEIGHT_MAX_KG,
            ],
            'tinggi_badan' => [
                'min' => self::HEIGHT_MIN_CM,
                'max' => self::HEIGHT_MAX_CM,
            ],
            'lingkar_lengan_atas' => [
                'min' => self::ARM_CIRCUMFERENCE_MIN_CM,
                'max' => self::ARM_CIRCUMFERENCE_MAX_CM,
            ],
            'lingkar_kepala' => [
                'min' => self::HEAD_CIRCUMFERENCE_MIN_CM,
                'max' => self::HEAD_CIRCUMFERENCE_MAX_CM,
            ],
        ];
    }
}
