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
                $retryAfter = (int) ($headers['Retry-After'] ?? 60);
                $message = 'Terlalu banyak permintaan kode OTP. Silakan coba lagi beberapa saat lagi.';

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
                    $retryAfter = (int) ($headers['Retry-After'] ?? 60);
                    $message = 'Terlalu banyak percobaan verifikasi OTP. Silakan tunggu beberapa menit lalu coba lagi.';

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
                    $retryAfter = (int) ($headers['Retry-After'] ?? 60);
                    $message = 'Terlalu banyak percobaan atur ulang kata sandi. Silakan coba lagi beberapa menit lagi.';

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