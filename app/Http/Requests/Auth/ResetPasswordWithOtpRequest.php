<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ResetPasswordWithOtpRequest extends FormRequest
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

        if (is_string($this->input('reset_token'))) {
            $merged['reset_token'] = trim($this->input('reset_token'));
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
            'reset_token' => ['sometimes', 'required', 'string', 'size:64'],
            'password' => ['bail', 'required', 'string', 'min:8', 'max:64', 'confirmed'],
            'password_confirmation' => ['bail', 'required', 'string', 'min:8', 'max:64'],
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
            'reset_token.required' => 'Token reset kata sandi wajib diisi.',
            'reset_token.size' => 'Token reset kata sandi tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.max' => 'Kata sandi baru maksimal 64 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi kata sandi baru wajib diisi.',
            'password_confirmation.min' => 'Konfirmasi kata sandi baru minimal 8 karakter.',
            'password_confirmation.max' => 'Konfirmasi kata sandi baru maksimal 64 karakter.',
        ];
    }
}