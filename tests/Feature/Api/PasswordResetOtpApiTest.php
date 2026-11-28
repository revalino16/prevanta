<?php

namespace Tests\Feature\Api;

use App\Mail\PasswordResetOtpMail;
use App\Models\Users;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PasswordResetOtpApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        Cache::flush();
        Carbon::setTestNow();
        $this->createTestSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_unregistered_email_returns_404_with_exact_error_and_sends_no_mail(): void
    {
        Mail::fake();

        $this->postJson('/api/forgot-password/request-otp', [
            'email' => 'tidak-terdaftar@example.test',
        ])
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Email Tidak Terdaftar. Silahkan Coba Masukkan Email yang benar.',
                'data' => [
                    'status' => 'not_found',
                ],
                'errors' => [
                    'email' => ['Email Tidak Terdaftar. Silahkan Coba Masukkan Email yang benar.'],
                ],
            ]);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    #[TestWith(['orang_tua'])]
    #[TestWith(['kader'])]
    #[TestWith(['bidan'])]
    public function test_complete_three_step_api_flow_works_for_all_roles_and_revokes_tokens_and_sessions(string $role): void
    {
        Mail::fake();

        $user = $this->createUserAccount([
            'nama' => 'Siti Aminah',
            'email' => $role.'@example.test',
            'role' => $role,
        ]);

        // Simulasikan token mobile (Sanctum) & sesi web aktif yang harus dicabut setelah reset
        $user->createToken('flutter-app');
        DB::table('sessions')->insert([
            'id' => 'active-session-'.$role,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'serialized-payload',
            'last_activity' => now()->timestamp,
        ]);

        // Tahap 1: Request OTP
        $step1 = $this->postJson('/api/forgot-password/request-otp', [
            'email' => strtoupper($user->email),
        ]);

        $step1
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Kode OTP 6 digit telah terkirim ke email Anda. Silakan periksa kotak masuk atau folder spam.',
                'data' => [
                    'status' => 'sent',
                    'email' => $user->email,
                    'expires_in_seconds' => 600,
                    'cooldown_remaining_seconds' => 60,
                ],
            ])
            ->assertJsonStructure([
                'data' => ['masked_email'],
            ]);

        $capturedOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use ($user, &$capturedOtp): bool {
            $capturedOtp = $mail->otpCode;

            return $mail->hasTo($user->email);
        });

        $this->assertNotNull($capturedOtp);

        // Tahap 2: Verify OTP
        $step2 = $this->postJson('/api/forgot-password/verify-otp', [
            'email' => $user->email,
            'otp' => $capturedOtp,
        ]);

        $step2
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Kode OTP valid. Silakan buat kata sandi baru Anda.',
                'data' => [
                    'status' => 'verified',
                    'email' => $user->email,
                    'expires_in_seconds' => 900,
                ],
            ])
            ->assertJsonStructure([
                'data' => ['reset_token', 'masked_email'],
            ]);

        $resetToken = (string) $step2->json('data.reset_token');
        $this->assertSame(64, strlen($resetToken));

        // Tahap 3: Reset Password
        $step3 = $this->postJson('/api/forgot-password/reset-password', [
            'email' => $user->email,
            'reset_token' => $resetToken,
            'password' => 'SandiBaru2026',
            'password_confirmation' => 'SandiBaru2026',
        ]);

        $step3
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.',
                'data' => [
                    'status' => 'reset',
                ],
            ]);

        $user->refresh();
        $this->assertFalse(Hash::check('SandiLama123!', $user->password));
        $this->assertTrue(Hash::check('SandiBaru2026', $user->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);

        // Reset token bersifat sekali pakai (single-use)
        $this->postJson('/api/forgot-password/reset-password', [
            'email' => $user->email,
            'reset_token' => $resetToken,
            'password' => 'SandiLain2026',
            'password_confirmation' => 'SandiLain2026',
        ])
            ->assertStatus(410)
            ->assertJson([
                'success' => false,
                'data' => [
                    'status' => 'invalid_token',
                ],
            ]);
    }

    public function test_api_requires_email_and_reset_token_fields_on_stateless_endpoints(): void
    {
        $this->postJson('/api/forgot-password/verify-otp', [
            'otp' => '123456',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/forgot-password/reset-password', [
            'password' => 'SandiBaru2026',
            'password_confirmation' => 'SandiBaru2026',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'reset_token']);
    }

    public function test_cooldown_returns_429_within_60_seconds_and_resend_works_after_60_seconds(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-28 08:00:00'));

        $user = $this->createUserAccount(['email' => 'cooldown@example.test']);

        $this->postJson('/api/forgot-password/request-otp', ['email' => $user->email])
            ->assertOk();

        // Kirim ulang pada detik ke-20 ditolak oleh cooldown 60 detik
        Carbon::setTestNow(Carbon::parse('2026-11-28 08:00:20'));

        $this->postJson('/api/forgot-password/request-otp', [
            'email' => $user->email,
            'is_resend' => true,
        ])
            ->assertTooManyRequests()
            ->assertHeader('Retry-After', '40')
            ->assertJson([
                'success' => false,
                'data' => [
                    'status' => 'cooldown',
                    'email' => $user->email,
                    'cooldown_remaining_seconds' => 40,
                    'retry_after_seconds' => 40,
                ],
            ]);

        Mail::assertSent(PasswordResetOtpMail::class, 1);

        // Setelah 61 detik, kirim ulang berhasil dengan pesan khusus resend
        Carbon::setTestNow(Carbon::parse('2026-11-28 08:01:01'));

        $this->postJson('/api/forgot-password/request-otp', [
            'email' => $user->email,
            'is_resend' => true,
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Kode OTP baru telah terkirim ke email Anda. Kode OTP lama sudah tidak berlaku.',
                'data' => [
                    'status' => 'sent',
                    'cooldown_remaining_seconds' => 60,
                ],
            ]);

        Mail::assertSent(PasswordResetOtpMail::class, 2);
    }

    public function test_wrong_otp_decrements_remaining_attempts_and_locks_on_fifth_attempt(): void
    {
        Mail::fake();

        $user = $this->createUserAccount(['email' => 'bruteforce@example.test']);

        $this->postJson('/api/forgot-password/request-otp', ['email' => $user->email])
            ->assertOk();

        $realOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$realOtp): bool {
            $realOtp = $mail->otpCode;

            return true;
        });

        $wrongOtp = $realOtp === '000000' ? '111111' : '000000';

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $remaining = 5 - $attempt;

            $this->postJson('/api/forgot-password/verify-otp', [
                'email' => $user->email,
                'otp' => $wrongOtp,
            ])
                ->assertUnprocessable()
                ->assertJson([
                    'success' => false,
                    'message' => "Kode OTP tidak sesuai. Sisa percobaan: {$remaining} kali.",
                    'data' => [
                        'status' => 'mismatch',
                        'remaining_attempts' => $remaining,
                    ],
                ]);
        }

        // Kesalahan ke-5 menghanguskan OTP
        $this->postJson('/api/forgot-password/verify-otp', [
            'email' => $user->email,
            'otp' => $wrongOtp,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Batas percobaan kode OTP telah habis. Silakan minta kode OTP baru.',
                'data' => [
                    'status' => 'locked',
                ],
            ]);

        // Percobaan ke-6 dengan OTP benar tetap ditolak
        $this->postJson('/api/forgot-password/verify-otp', [
            'email' => $user->email,
            'otp' => $realOtp,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'data' => [
                    'status' => 'invalid',
                ],
            ]);
    }

    public function test_expired_otp_after_ten_minutes_returns_422_expired(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-28 09:00:00'));

        $user = $this->createUserAccount(['email' => 'expired@example.test']);

        $this->postJson('/api/forgot-password/request-otp', ['email' => $user->email])
            ->assertOk();

        $realOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$realOtp): bool {
            $realOtp = $mail->otpCode;

            return true;
        });

        Carbon::setTestNow(Carbon::parse('2026-11-28 09:11:00'));

        $this->postJson('/api/forgot-password/verify-otp', [
            'email' => $user->email,
            'otp' => $realOtp,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Kode OTP telah kedaluwarsa. Silakan kirim ulang kode OTP baru.',
                'data' => [
                    'status' => 'expired',
                ],
            ]);
    }

    public function test_hourly_rate_limit_returns_429_json_with_retry_after_seconds_and_human_duration(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-28 10:00:00'));

        // Uji batas 5x/jam per email
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/forgot-password/request-otp', [
                'email' => 'limit-email@example.test',
            ])->assertNotFound();
        }

        $this->postJson('/api/forgot-password/request-otp', [
            'email' => 'limit-email@example.test',
        ])
            ->assertTooManyRequests()
            ->assertJson([
                'success' => false,
                'message' => 'Terlalu banyak permintaan kode OTP. Silakan coba lagi dalam 1 jam.',
            ])
            ->assertJsonStructure([
                'data' => ['retry_after_seconds'],
            ]);

        // Maju 15 menit -> sisa waktu tunggu menjadi 45 menit
        Carbon::setTestNow(Carbon::parse('2026-11-28 10:15:00'));

        $this->postJson('/api/forgot-password/request-otp', [
            'email' => 'limit-email@example.test',
        ])
            ->assertTooManyRequests()
            ->assertJson([
                'success' => false,
                'message' => 'Terlalu banyak permintaan kode OTP. Silakan coba lagi dalam 45 menit.',
            ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createUserAccount(array $overrides = []): Users
    {
        return Users::create([
            'nama' => $overrides['nama'] ?? 'Siti Aminah',
            'email' => $overrides['email'] ?? 'orangtua@example.test',
            'password' => Hash::make($overrides['password'] ?? 'SandiLama123!'),
            'no_hp' => $overrides['no_hp'] ?? '081234567001',
            'role' => $overrides['role'] ?? 'orang_tua',
        ]);
    }

    private function createTestSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('no_hp', 20)->nullable();
            $table->string('role', 30);
        });

        Schema::create('password_reset_otps', function (Blueprint $table): void {
            $table->id();
            $table->string('email', 100);
            $table->string('otp_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->char('reset_token_hash', 64)->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->string('request_ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
}
