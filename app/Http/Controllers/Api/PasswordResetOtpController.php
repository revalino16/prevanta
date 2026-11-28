<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ResetPasswordWithOtpApiRequest;
use App\Http\Requests\Api\VerifyPasswordOtpApiRequest;
use App\Http\Requests\Auth\RequestPasswordOtpRequest;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\JsonResponse;

class PasswordResetOtpController extends Controller
{
    public function __construct(
        public PasswordResetOtpService $otpService,
    ) {}

    /**
     * POST /api/forgot-password/request-otp
     * Tahap 1 & Kirim Ulang: Memeriksa email, mengecek cooldown 60 detik, dan mengirim 6 digit OTP.
     */
    public function requestOtp(RequestPasswordOtpRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $result = $this->otpService->requestOtp($email, $request->ip());

        if ($result['status'] === 'not_found') {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => [
                    'status' => 'not_found',
                ],
                'errors' => [
                    'email' => [$result['message']],
                ],
            ], 404);
        }

        if ($result['status'] === 'cooldown') {
            $cooldownSeconds = (int) ($result['cooldown_remaining_seconds'] ?? PasswordResetOtpService::RESEND_COOLDOWN_SECONDS);

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => [
                    'status' => 'cooldown',
                    'email' => $result['email'],
                    'masked_email' => $this->otpService->maskEmail($result['email']),
                    'cooldown_remaining_seconds' => $cooldownSeconds,
                    'retry_after_seconds' => $cooldownSeconds,
                ],
                'errors' => [
                    'email' => [$result['message']],
                ],
            ], 429, ['Retry-After' => (string) $cooldownSeconds]);
        }

        if ($result['status'] === 'mail_failed') {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => [
                    'status' => 'mail_failed',
                ],
            ], 503);
        }

        $message = $request->boolean('is_resend')
            ? 'Kode OTP baru telah terkirim ke email Anda. Kode OTP lama sudah tidak berlaku.'
            : $result['message'];

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'status' => 'sent',
                'email' => $result['email'],
                'masked_email' => $this->otpService->maskEmail($result['email']),
                'expires_in_seconds' => $result['expires_in_seconds'],
                'cooldown_remaining_seconds' => $result['cooldown_remaining_seconds'],
            ],
        ]);
    }

    /**
     * POST /api/forgot-password/verify-otp
     * Tahap 2: Memverifikasi 6 digit OTP dan menerbitkan reset_token sementara (15 menit).
     */
    public function verifyOtp(VerifyPasswordOtpApiRequest $request): JsonResponse
    {
        $result = $this->otpService->verifyOtp(
            email: $request->validated('email'),
            otp: $request->validated('otp'),
        );

        if ($result['status'] !== 'verified') {
            $data = [
                'status' => $result['status'],
            ];

            if (isset($result['remaining_attempts'])) {
                $data['remaining_attempts'] = $result['remaining_attempts'];
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => $data,
                'errors' => [
                    'otp' => [$result['message']],
                ],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'status' => 'verified',
                'email' => $result['email'],
                'masked_email' => $this->otpService->maskEmail($result['email']),
                'reset_token' => $result['reset_token'],
                'expires_in_seconds' => $result['expires_in_seconds'],
            ],
        ]);
    }

    /**
     * POST /api/forgot-password/reset-password
     * Tahap 3: Menyimpan kata sandi baru dan mencabut semua token Sanctum serta sesi web aktif.
     */
    public function resetPassword(ResetPasswordWithOtpApiRequest $request): JsonResponse
    {
        $result = $this->otpService->resetPassword(
            email: $request->validated('email'),
            plainResetToken: $request->validated('reset_token'),
            newPassword: $request->validated('password'),
        );

        if ($result['status'] !== 'reset') {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => [
                    'status' => $result['status'],
                ],
                'errors' => [
                    'reset_token' => [$result['message']],
                ],
            ], 410);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'status' => 'reset',
            ],
        ]);
    }
}
