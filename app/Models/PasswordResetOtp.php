<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class PasswordResetOtp extends Model
{
    use Prunable;

    protected $table = 'password_reset_otps';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'otp_hash',
        'attempts',
        'max_attempts',
        'expires_at',
        'verified_at',
        'reset_token_hash',
        'reset_token_expires_at',
        'used_at',
        'invalidated_at',
        'request_ip',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'otp_hash',
        'reset_token_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'reset_token_expires_at' => 'datetime',
            'used_at' => 'datetime',
            'invalidated_at' => 'datetime',
        ];
    }

    /**
     * Query record OTP lama (> 24 jam) untuk dibersihkan otomatis.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subHours(24));
    }

    /**
     * Mengecek apakah masa berlaku kode OTP sudah habis.
     */
    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }

    /**
     * Mengecek apakah percobaan verifikasi OTP sudah mencapai batas maksimal.
     */
    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Mengecek apakah kode OTP masih aktif dan dapat diverifikasi.
     */
    public function isUsable(): bool
    {
        return $this->used_at === null
            && $this->invalidated_at === null
            && $this->verified_at === null
            && ! $this->isExpired()
            && ! $this->hasExceededAttempts();
    }

    /**
     * Mengecek apakah reset token valid, belum dipakai, dan belum kedaluwarsa.
     */
    public function isResetTokenValid(string $plainToken): bool
    {
        if (
            $this->verified_at === null
            || $this->used_at !== null
            || $this->invalidated_at !== null
            || $this->reset_token_hash === null
            || $this->reset_token_expires_at === null
            || $this->reset_token_expires_at->isPast()
        ) {
            return false;
        }

        return hash_equals($this->reset_token_hash, hash('sha256', $plainToken));
    }
}