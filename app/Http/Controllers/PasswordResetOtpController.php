<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RequestPasswordOtpRequest;
use App\Http\Requests\Auth\ResetPasswordWithOtpRequest;
use App\Http\Requests\Auth\VerifyPasswordOtpRequest;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetOtpController extends Controller
{
    public function __construct(
        public PasswordResetOtpService $otpService,
    ) {}

    /**
     * Langkah 1: Menampilkan form input email untuk meminta kode OTP.
     */
    public function showRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Langkah 1 (Submit): Memeriksa email, mengecek cooldown, dan mengirim OTP ke email.
     */
    public function sendOtp(RequestPasswordOtpRequest $request): RedirectResponse
    {
        $email = $request->validated('email');
        $result = $this->otpService->requestOtp($email, $request->ip());

        if ($result['status'] !== 'sent') {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $result['message']]);
        }

        $request->session()->put('password_reset.email', $result['email']);
        $request->session()->forget('password_reset.token');

        return redirect()
            ->route('password.otp.form')
            ->with('otp_status', $result['message']);
    }

    /**
     * Langkah 2: Menampilkan form input 6 digit OTP.
     */
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset.email', '');

        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Silakan masukkan email akun Anda terlebih dahulu.']);
        }

        return view('auth.verify-otp', [
            'maskedEmail' => $this->otpService->maskEmail($email),
            'cooldownSeconds' => $this->otpService->getRemainingCooldownSeconds($email),
        ]);
    }

    /**
     * Langkah 2 (Submit): Memverifikasi kode OTP 6 digit.
     */
    public function verifyOtp(VerifyPasswordOtpRequest $request): RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset.email', '');

        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Sesi verifikasi telah berakhir. Silakan masukkan email Anda kembali.']);
        }

        $result = $this->otpService->verifyOtp($email, $request->validated('otp'));

        if ($result['status'] !== 'verified') {
            return back()->withErrors(['otp' => $result['message']]);
        }

        $request->session()->put('password_reset.token', $result['reset_token']);

        return redirect()
            ->route('password.reset.form')
            ->with('otp_verified', $result['message']);
    }

    /**
     * Langkah 2 (Kirim Ulang): Mengirim ulang kode OTP baru dengan perlindungan cooldown 60 detik.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset.email', '');

        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Sesi verifikasi telah berakhir. Silakan masukkan email Anda kembali.']);
        }

        $result = $this->otpService->requestOtp($email, $request->ip());

        if ($result['status'] !== 'sent') {
            return redirect()
                ->route('password.otp.form')
                ->withErrors(['resend' => $result['message']]);
        }

        $request->session()->forget('password_reset.token');

        return redirect()
            ->route('password.otp.form')
            ->with('otp_status', 'Kode OTP baru telah terkirim ke email Anda. Kode OTP lama sudah tidak berlaku.');
    }

    /**
     * Langkah 3: Menampilkan form pembuatan kata sandi baru.
     */
    public function showResetForm(Request $request): View|RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset.email', '');
        $token = (string) $request->session()->get('password_reset.token', '');

        if ($email === '' || $token === '' || ! $this->otpService->hasValidResetToken($email, $token)) {
            $request->session()->forget('password_reset.token');

            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Sesi atur ulang kata sandi tidak valid atau telah kedaluwarsa. Silakan ulangi permintaan OTP.']);
        }

        return view('auth.reset-password', [
            'maskedEmail' => $this->otpService->maskEmail($email),
        ]);
    }

    /**
     * Langkah 3 (Submit): Menyimpan kata sandi baru dan mengarahkan kembali ke halaman login.
     */
    public function resetPassword(ResetPasswordWithOtpRequest $request): RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset.email', '');
        $token = (string) $request->session()->get('password_reset.token', '');

        if ($email === '' || $token === '') {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Sesi atur ulang kata sandi telah berakhir. Silakan ulangi permintaan OTP.']);
        }

        $result = $this->otpService->resetPassword($email, $token, $request->validated('password'));

        if ($result['status'] !== 'reset') {
            $request->session()->forget(['password_reset.email', 'password_reset.token']);

            return redirect()
                ->route('password.request')
                ->withErrors(['email' => $result['message']]);
        }

        $request->session()->forget(['password_reset.email', 'password_reset.token']);
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', $result['message']);
    }
}