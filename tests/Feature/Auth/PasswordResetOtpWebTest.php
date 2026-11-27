<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\Users;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PasswordResetOtpWebTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $this->withoutVite();
        Cache::flush();
        Carbon::setTestNow();
        $this->createTestSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_login_page_links_to_forgot_password_and_request_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), false)
            ->assertSeeText('Lupa kata sandi?');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertViewIs('auth.forgot-password')
            ->assertSeeText('Langkah 1 dari 3')
            ->assertSeeText('Lupa Kata Sandi?');
    }

    public function test_unregistered_email_shows_exact_error_and_does_not_send_mail(): void
    {
        Mail::fake();

        $response = $this->from(route('password.request'))
            ->post(route('password.otp.send'), [
                'email' => 'tidak-terdaftar@example.test',
            ]);

        $response
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors([
                'email' => 'Email Tidak Terdaftar. Silahkan Coba Masukkan Email yang benar.',
            ]);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    #[TestWith(['orang_tua'])]
    #[TestWith(['kader'])]
    #[TestWith(['bidan'])]
    public function test_complete_three_step_reset_flow_works_for_all_roles_and_revokes_sessions_and_tokens(string $role): void
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

        // Tahap 1: Minta OTP
        $step1 = $this->post(route('password.otp.send'), [
            'email' => strtoupper($user->email),
        ]);

        $step1
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHas('otp_status');

        $capturedOtp = null;

        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use ($user, &$capturedOtp): bool {
            $capturedOtp = $mail->otpCode;

            $mail->assertSeeInHtml($mail->otpCode);
            $mail->assertSeeInHtml('Prevanta');
            $mail->assertSeeInHtml('Posyandu Jambu 77');
            $mail->assertSeeInText($mail->otpCode);

            return $mail->hasTo($user->email);
        });

        $this->assertNotNull($capturedOtp);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $capturedOtp);

        // Pastikan OTP disimpan sebagai hash, bukan plain text
        $otpRecord = PasswordResetOtp::firstOrFail();
        $this->assertNotSame($capturedOtp, $otpRecord->otp_hash);
        $this->assertTrue(Hash::check($capturedOtp, $otpRecord->otp_hash));

        // Pastikan halaman verifikasi tampil & menyamarkan email
        $this->get(route('password.otp.form'))
            ->assertOk()
            ->assertViewIs('auth.verify-otp')
            ->assertSeeText('Langkah 2 dari 3');

        // Tahap 2: Verifikasi OTP
        $step2 = $this->post(route('password.otp.verify'), [
            'otp' => $capturedOtp,
        ]);

        $step2
            ->assertRedirect(route('password.reset.form'))
            ->assertSessionHas('otp_verified');

        $otpRecord->refresh();
        $this->assertNotNull($otpRecord->verified_at);
        $this->assertNotNull($otpRecord->reset_token_hash);

        $this->get(route('password.reset.form'))
            ->assertOk()
            ->assertViewIs('auth.reset-password')
            ->assertSeeText('Langkah 3 dari 3');

        // Tahap 3: Simpan kata sandi baru
        $step3 = $this->post(route('password.reset.update'), [
            'password' => 'SandiBaru2026',
            'password_confirmation' => 'SandiBaru2026',
        ]);

        $step3
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');

        $user->refresh();
        $otpRecord->refresh();

        $this->assertFalse(Hash::check('SandiLama123!', $user->password));
        $this->assertTrue(Hash::check('SandiBaru2026', $user->password));
        $this->assertNotNull($otpRecord->used_at);
        $this->assertNull($otpRecord->reset_token_hash);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    public function test_cooldown_blocks_duplicate_otp_within_60_seconds_and_allows_after_60_seconds(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-28 08:00:00'));

        $user = $this->createUserAccount(['email' => 'cooldown@example.test']);

        // Permintaan pertama berhasil
        $this->post(route('password.otp.send'), ['email' => $user->email])
            ->assertRedirect(route('password.otp.form'));

        $firstOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$firstOtp): bool {
            $firstOtp = $mail->otpCode;

            return true;
        });
        Mail::assertSent(PasswordResetOtpMail::class, 1);

        // Permintaan ulang saat 20 detik (dari halaman 1) ditolak oleh cooldown
        Carbon::setTestNow(Carbon::parse('2026-11-28 08:00:20'));
        $this->from(route('password.request'))
            ->post(route('password.otp.send'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        // Klik tombol Kirim Ulang saat 25 detik juga ditolak oleh cooldown
        Carbon::setTestNow(Carbon::parse('2026-11-28 08:00:25'));
        $this->post(route('password.otp.resend'))
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHasErrors('resend');

        // Tetap hanya 1 email yang terkirim (tidak menumpuk)
        Mail::assertSent(PasswordResetOtpMail::class, 1);

        // Setelah 61 detik, kirim ulang berhasil & menghanguskan OTP pertama
        Carbon::setTestNow(Carbon::parse('2026-11-28 08:01:01'));
        $this->post(route('password.otp.resend'))
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHas('otp_status');

        Mail::assertSent(PasswordResetOtpMail::class, 2);

        $otps = PasswordResetOtp::orderBy('id')->get();
        $this->assertCount(2, $otps);
        $this->assertNotNull($otps[0]->invalidated_at);
        $this->assertNull($otps[1]->invalidated_at);

        // OTP lama tidak lagi berlaku
        $this->post(route('password.otp.verify'), ['otp' => $firstOtp])
            ->assertSessionHasErrors('otp');
    }

    public function test_wrong_otp_locks_out_after_five_attempts(): void
    {
        Mail::fake();

        $user = $this->createUserAccount(['email' => 'bruteforce@example.test']);

        $this->post(route('password.otp.send'), ['email' => $user->email])
            ->assertRedirect(route('password.otp.form'));

        $realOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$realOtp): bool {
            $realOtp = $mail->otpCode;

            return true;
        });

        $wrongOtp = $realOtp === '000000' ? '111111' : '000000';

        // 4 kesalahan pertama menaikkan attempts
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $remaining = 5 - $attempt;
            $this->from(route('password.otp.form'))
                ->post(route('password.otp.verify'), ['otp' => $wrongOtp])
                ->assertRedirect(route('password.otp.form'))
                ->assertSessionHasErrors([
                    'otp' => "Kode OTP tidak sesuai. Sisa percobaan: {$remaining} kali.",
                ]);
        }

        // Kesalahan ke-5 langsung menghanguskan OTP
        $this->from(route('password.otp.form'))
            ->post(route('password.otp.verify'), ['otp' => $wrongOtp])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHasErrors([
                'otp' => 'Batas percobaan kode OTP telah habis. Silakan minta kode OTP baru.',
            ]);

        $otpRecord = PasswordResetOtp::firstOrFail();
        $this->assertSame(5, $otpRecord->attempts);
        $this->assertNotNull($otpRecord->invalidated_at);

        // Percobaan ke-6 memakai kode yang benar pun tetap ditolak
        $this->from(route('password.otp.form'))
            ->post(route('password.otp.verify'), ['otp' => $realOtp])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHasErrors('otp');
    }

    public function test_expired_otp_after_ten_minutes_is_rejected(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-28 09:00:00'));

        $user = $this->createUserAccount(['email' => 'expired@example.test']);

        $this->post(route('password.otp.send'), ['email' => $user->email])
            ->assertRedirect(route('password.otp.form'));

        $realOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$realOtp): bool {
            $realOtp = $mail->otpCode;

            return true;
        });

        // Maju 11 menit melewati masa berlaku 10 menit
        Carbon::setTestNow(Carbon::parse('2026-11-28 09:11:00'));

        $this->from(route('password.otp.form'))
            ->post(route('password.otp.verify'), ['otp' => $realOtp])
            ->assertRedirect(route('password.otp.form'))
            ->assertSessionHasErrors([
                'otp' => 'Kode OTP telah kedaluwarsa. Silakan kirim ulang kode OTP baru.',
            ]);
    }

    public function test_password_validation_enforces_8_to_64_characters_and_confirmation(): void
    {
        Mail::fake();

        $user = $this->createUserAccount(['email' => 'validasi@example.test']);

        $this->post(route('password.otp.send'), ['email' => $user->email]);

        $realOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$realOtp): bool {
            $realOtp = $mail->otpCode;

            return true;
        });

        $this->post(route('password.otp.verify'), ['otp' => $realOtp])
            ->assertRedirect(route('password.reset.form'));

        // Kurang dari 8 karakter
        $this->from(route('password.reset.form'))
            ->post(route('password.reset.update'), [
                'password' => '1234567',
                'password_confirmation' => '1234567',
            ])
            ->assertRedirect(route('password.reset.form'))
            ->assertSessionHasErrors('password');

        // Lebih dari 64 karakter
        $tooLong = str_repeat('a', 65);
        $this->from(route('password.reset.form'))
            ->post(route('password.reset.update'), [
                'password' => $tooLong,
                'password_confirmation' => $tooLong,
            ])
            ->assertRedirect(route('password.reset.form'))
            ->assertSessionHasErrors('password');

        // Konfirmasi tidak cocok
        $this->from(route('password.reset.form'))
            ->post(route('password.reset.update'), [
                'password' => 'SandiBaru2026',
                'password_confirmation' => 'Berbeda2026',
            ])
            ->assertRedirect(route('password.reset.form'))
            ->assertSessionHasErrors('password');
    }

    public function test_direct_access_to_verify_or_reset_page_without_session_redirects_to_request_page(): void
    {
        $this->get(route('password.otp.form'))
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        $this->get(route('password.reset.form'))
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }

    public function test_hourly_rate_limit_blocks_excessive_requests_from_same_ip(): void
    {
        Mail::fake();

        for ($i = 1; $i <= 15; $i++) {
            $this->from(route('password.request'))
                ->post(route('password.otp.send'), [
                    'email' => "unknown{$i}@example.test",
                ])
                ->assertRedirect(route('password.request'))
                ->assertSessionHasErrors([
                    'email' => 'Email Tidak Terdaftar. Silahkan Coba Masukkan Email yang benar.',
                ]);
        }

        // Permintaan ke-16 dari IP yang sama dalam 1 jam terkena rate limit
        $this->from(route('password.request'))
            ->post(route('password.otp.send'), [
                'email' => 'unknown16@example.test',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors([
                'email' => 'Terlalu banyak permintaan kode OTP. Silakan coba lagi beberapa saat lagi.',
            ]);
    }

    public function test_old_otp_records_older_than_24_hours_are_pruned(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-28 12:00:00'));

        $oldOtp = PasswordResetOtp::create([
            'email' => 'lama@example.test',
            'otp_hash' => Hash::make('123456'),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->subHours(25),
        ]);
        $oldOtp->forceFill(['created_at' => now()->subHours(25)])->save();

        $freshOtp = PasswordResetOtp::create([
            'email' => 'baru@example.test',
            'otp_hash' => Hash::make('654321'),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes(10),
        ]);

        Artisan::call('model:prune', [
            '--model' => [PasswordResetOtp::class],
        ]);

        $this->assertDatabaseMissing('password_reset_otps', ['id' => $oldOtp->id]);
        $this->assertDatabaseHas('password_reset_otps', ['id' => $freshOtp->id]);
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
