<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePasswordResetRateLimiters();
    }

    /**
     * Mendaftarkan rate limiter berlapis untuk fitur lupa kata sandi OTP.
     */
    protected function configurePasswordResetRateLimiters(): void
    {
        RateLimiter::for('password-otp-request', function (Request $request): array {
            $email = $this->resolvePasswordResetEmail($request);
            $throttleResponse = function (Request $request, array $headers) {
                $retryAfter = (int) ($headers['Retry-After'] ?? $headers['retry-after'] ?? 60);
                $waitText = $this->formatRetryAfter($retryAfter);
                $message = "Terlalu banyak permintaan kode OTP. Silakan coba lagi dalam {$waitText}.";

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'data' => [
                            'retry_after_seconds' => $retryAfter,
                        ],
                    ], 429, $headers);
                }

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => $message]);
            };

            $limits = [
                Limit::perHour(15)
                    ->by('password-otp-ip:'.$request->ip())
                    ->response($throttleResponse),
            ];

            if ($email !== '') {
                $limits[] = Limit::perHour(5)
                    ->by('password-otp-email:'.$email)
                    ->response($throttleResponse);
            }

            return $limits;
        });

        RateLimiter::for('password-otp-verify', function (Request $request): Limit {
            $email = $this->resolvePasswordResetEmail($request);

            return Limit::perMinutes(15, 10)
                ->by('password-otp-verify:'.$email.'|'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    $retryAfter = (int) ($headers['Retry-After'] ?? $headers['retry-after'] ?? 60);
                    $waitText = $this->formatRetryAfter($retryAfter);
                    $message = "Terlalu banyak percobaan verifikasi OTP. Silakan coba lagi dalam {$waitText}.";

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'data' => [
                                'retry_after_seconds' => $retryAfter,
                            ],
                        ], 429, $headers);
                    }

                    return back()->withErrors(['otp' => $message]);
                });
        });

        RateLimiter::for('password-otp-reset', function (Request $request): Limit {
            $email = $this->resolvePasswordResetEmail($request);

            return Limit::perMinutes(15, 5)
                ->by('password-otp-reset:'.$email.'|'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    $retryAfter = (int) ($headers['Retry-After'] ?? $headers['retry-after'] ?? 60);
                    $waitText = $this->formatRetryAfter($retryAfter);
                    $message = "Terlalu banyak percobaan atur ulang kata sandi. Silakan coba lagi dalam {$waitText}.";

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'data' => [
                                'retry_after_seconds' => $retryAfter,
                            ],
                        ], 429, $headers);
                    }

                    return back()->withErrors(['password' => $message]);
                });
        });
    }

    /**
     * Memformat sisa detik rate limiter menjadi teks durasi ramah pengguna (detik / menit / jam).
     */
    protected function formatRetryAfter(int $seconds): string
    {
        $seconds = max(1, $seconds);

        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        $totalMinutes = (int) ceil($seconds / 60);

        if ($totalMinutes < 60) {
            return "{$totalMinutes} menit";
        }

        $hours = intdiv($totalMinutes, 60);
        $remainingMinutes = $totalMinutes % 60;

        if ($remainingMinutes === 0) {
            return "{$hours} jam";
        }

        return "{$hours} jam {$remainingMinutes} menit";
    }

    /**
     * Mengambil email dari input request atau session reset password secara aman (Web & API).
     */
    protected function resolvePasswordResetEmail(Request $request): string
    {
        $sessionEmail = $request->hasSession()
            ? (string) $request->session()->get('password_reset.email', '')
            : '';

        return Str::lower(trim((string) ($request->input('email') ?: $sessionEmail)));
    }
}
