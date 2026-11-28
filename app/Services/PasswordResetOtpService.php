<?php

namespace App\Services;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetOtpService
{
    public const OTP_TTL_MINUTES = 10;

    public const RESET_TOKEN_TTL_MINUTES = 15;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const MAX_ATTEMPTS = 5;

    /**
     * Menormalisasi format email ke huruf kecil tanpa spasi di awal/akhir.
     */
    public function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    /**
     * Menyamarkan alamat email untuk tampilan UI (contoh: s***@gmail.com).
     */
    public function maskEmail(string $email): string
    {
        $normalized = $this->normalizeEmail($email);

        if (! str_contains($normalized, '@')) {
            return $normalized;
        }

        [$local, $domain] = explode('@', $normalized, 2);
        $length = strlen($local);

        if ($length <= 2) {
            return substr($local, 0, 1).'***@'.$domain;
        }

        return substr($local, 0, 2).str_repeat('*', min(5, $length - 2)).'@'.$domain;
    }

    /**
     * Menghitung sisa waktu cooldown (detik) sebelum email boleh membuat OTP baru.
     */
    public function getRemainingCooldownSeconds(string $email): int
    {
        $normalizedEmail = $this->normalizeEmail($email);

        $latestOtp = PasswordResetOtp::where('email', $normalizedEmail)
            ->latest('created_at')
            ->latest('id')
            ->first();

        if (! $latestOtp || $latestOtp->created_at === null) {
            return 0;
        }

        $availableAt = $latestOtp->created_at->copy()->addSeconds(self::RESEND_COOLDOWN_SECONDS);

        if (! $availableAt->isFuture()) {
            return 0;
        }

        return (int) max(0, ceil(now()->diffInSeconds($availableAt, false)));
    }

    /**
     * Tahap 1: Memeriksa email, mengecek cooldown, membuat OTP 6 digit, dan mengirim email.
     *
     * @return array{
     *     status: 'sent'|'not_found'|'cooldown'|'mail_failed',
     *     message: string,
     *     email?: string,
     *     expires_in_seconds?: int,
     *     cooldown_remaining_seconds?: int
     * }
     */
    public function requestOtp(string $email, ?string $ipAddress = null): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $user = Users::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (! $user) {
            return [
                'status' => 'not_found',
                'message' => 'Email Tidak Terdaftar. Silahkan Coba Masukkan Email yang benar.',
            ];
        }

        $cooldownSeconds = $this->getRemainingCooldownSeconds($normalizedEmail);

        if ($cooldownSeconds > 0) {
            return [
                'status' => 'cooldown',
                'message' => "Mohon tunggu {$cooldownSeconds} detik sebelum meminta kode OTP baru.",
                'email' => $normalizedEmail,
                'cooldown_remaining_seconds' => $cooldownSeconds,
            ];
        }

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            DB::transaction(function () use ($normalizedEmail, $user, $otpCode, $ipAddress): void {
                PasswordResetOtp::where('email', $normalizedEmail)
                    ->whereNull('used_at')
                    ->whereNull('invalidated_at')
                    ->update(['invalidated_at' => now()]);

                PasswordResetOtp::create([
                    'email' => $normalizedEmail,
                    'otp_hash' => Hash::make($otpCode),
                    'attempts' => 0,
                    'max_attempts' => self::MAX_ATTEMPTS,
                    'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
                    'request_ip' => $ipAddress,
                ]);

                Mail::to($user->email)->send(new PasswordResetOtpMail(
                    userName: (string) $user->nama,
                    otpCode: $otpCode,
                    expiresInMinutes: self::OTP_TTL_MINUTES,
                ));
            });
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim email OTP reset kata sandi.', [
                'email' => $normalizedEmail,
                'error' => $exception->getMessage(),
            ]);

            return [
                'status' => 'mail_failed',
                'message' => 'Gagal mengirim email OTP saat ini. Silakan periksa koneksi atau coba beberapa saat lagi.',
            ];
        }

        return [
            'status' => 'sent',
            'message' => 'Kode OTP 6 digit telah terkirim ke email Anda. Silakan periksa kotak masuk atau folder spam.',
            'email' => $normalizedEmail,
            'expires_in_seconds' => self::OTP_TTL_MINUTES * 60,
            'cooldown_remaining_seconds' => self::RESEND_COOLDOWN_SECONDS,
        ];
    }

    /**
     * Tahap 2: Memverifikasi kode OTP 6 digit dan menerbitkan reset token sementara.
     *
     * @return array{
     *     status: 'verified'|'invalid'|'expired'|'locked'|'mismatch',
     *     message: string,
     *     email?: string,
     *     reset_token?: string,
     *     expires_in_seconds?: int,
     *     remaining_attempts?: int
     * }
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $normalizedEmail = $this->normalizeEmail($email);

        $record = PasswordResetOtp::where('email', $normalizedEmail)
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $record) {
            return [
                'status' => 'invalid',
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa. Silakan minta kode OTP baru.',
            ];
        }

        if ($record->isExpired()) {
            $record->update(['invalidated_at' => now()]);

            return [
                'status' => 'expired',
                'message' => 'Kode OTP telah kedaluwarsa. Silakan kirim ulang kode OTP baru.',
            ];
        }

        if ($record->hasExceededAttempts()) {
            $record->update(['invalidated_at' => now()]);

            return [
                'status' => 'locked',
                'message' => 'Batas percobaan kode OTP telah habis. Silakan minta kode OTP baru.',
            ];
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            $newAttempts = $record->attempts + 1;
            $updateData = ['attempts' => $newAttempts];

            if ($newAttempts >= $record->max_attempts) {
                $updateData['invalidated_at'] = now();
            }

            $record->update($updateData);

            if ($newAttempts >= $record->max_attempts) {
                return [
                    'status' => 'locked',
                    'message' => 'Batas percobaan kode OTP telah habis. Silakan minta kode OTP baru.',
                ];
            }

            $remaining = $record->max_attempts - $newAttempts;

            return [
                'status' => 'mismatch',
                'message' => "Kode OTP tidak sesuai. Sisa percobaan: {$remaining} kali.",
                'remaining_attempts' => $remaining,
            ];
        }

        $plainResetToken = Str::random(64);

        $record->update([
            'verified_at' => now(),
            'reset_token_hash' => hash('sha256', $plainResetToken),
            'reset_token_expires_at' => now()->addMinutes(self::RESET_TOKEN_TTL_MINUTES),
        ]);

        return [
            'status' => 'verified',
            'message' => 'Kode OTP valid. Silakan buat kata sandi baru Anda.',
            'email' => $normalizedEmail,
            'reset_token' => $plainResetToken,
            'expires_in_seconds' => self::RESET_TOKEN_TTL_MINUTES * 60,
        ];
    }

    /**
     * Mengecek apakah kombinasi email & reset token masih valid sebelum menampilkan form Tahap 3.
     */
    public function hasValidResetToken(string $email, string $plainResetToken): bool
    {
        $normalizedEmail = $this->normalizeEmail($email);

        $record = PasswordResetOtp::where('email', $normalizedEmail)
            ->where('reset_token_hash', hash('sha256', $plainResetToken))
            ->latest('id')
            ->first();

        return $record !== null && $record->isResetTokenValid($plainResetToken);
    }

    /**
     * Tahap 3: Menyimpan kata sandi baru dan mencabut seluruh sesi serta token aktif user.
     *
     * @return array{
     *     status: 'reset'|'invalid_token',
     *     message: string
     * }
     */
    public function resetPassword(string $email, string $plainResetToken, string $newPassword): array
    {
        $normalizedEmail = $this->normalizeEmail($email);

        return DB::transaction(function () use ($normalizedEmail, $plainResetToken, $newPassword): array {
            $record = PasswordResetOtp::where('email', $normalizedEmail)
                ->where('reset_token_hash', hash('sha256', $plainResetToken))
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $record || ! $record->isResetTokenValid($plainResetToken)) {
                return [
                    'status' => 'invalid_token',
                    'message' => 'Sesi atur ulang kata sandi tidak valid atau telah kedaluwarsa. Silakan ulangi permintaan OTP.',
                ];
            }

            $user = Users::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

            if (! $user) {
                return [
                    'status' => 'invalid_token',
                    'message' => 'Akun tidak ditemukan. Silakan ulangi permintaan OTP.',
                ];
            }

            $user->password = Hash::make($newPassword);
            $user->save();

            $record->update([
                'used_at' => now(),
                'reset_token_hash' => null,
            ]);

            PasswordResetOtp::where('email', $normalizedEmail)
                ->where('id', '!=', $record->id)
                ->whereNull('used_at')
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);

            // Cabut semua token Sanctum (mobile) milik user
            $user->tokens()->delete();

            // Cabut semua sesi web aktif user di tabel sessions
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            return [
                'status' => 'reset',
                'message' => 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.',
            ];
        });
    }
}