<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class VerifyPasswordOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if (is_string($this->input('email'))) {
            $merged['email'] = Str::lower(trim($this->input('email')));
        }

        if (is_string($this->input('otp'))) {
            $merged['otp'] = preg_replace('/\D+/', '', trim($this->input('otp'))) ?? '';
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'required', 'string', 'email', 'max:100'],
            'otp' => ['bail', 'required', 'digits:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus berupa tepat 6 digit angka.',
        ];
    }
}