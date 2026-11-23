<?php

namespace App\Http\Requests\OrangTua;

use App\Models\Users;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateProfilOrangTuaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'orang_tua';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Users|null $user */
        $user = $this->user();
        $orangTua = $user?->orangTua;

        return [
            'nama' => ['bail', 'required', 'string', 'min:2', 'max:60', 'regex:/^[a-zA-Z]+(?:\s[a-zA-Z]+)*$/'],
            'nik' => ['bail', 'required', 'digits:16', Rule::unique('orang_tua', 'nik')->ignore($orangTua?->id)],
            'email' => ['bail', 'required', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user?->id)],
            'no_hp' => ['bail', 'required', 'digits_between:8,13'],
            'foto_profil' => [
                'bail',
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max('2mb'),
            ],
            'hapus_foto' => ['nullable', 'boolean'],
            'current_password' => [
                'bail',
                'required_with:password,password_confirmation',
                'nullable',
                'string',
                'max:72',
                function (string $attribute, mixed $value, Closure $fail) use ($user): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    if ($user === null || ! Hash::check($value, (string) $user->password)) {
                        $fail('Kata sandi saat ini tidak sesuai.');
                    }
                },
            ],
            'password' => [
                'bail',
                'required_with:current_password,password_confirmation',
                'nullable',
                'string',
                'min:8',
                'max:64',
                'different:current_password',
                'confirmed',
            ],
            'password_confirmation' => [
                'bail',
                'required_with:current_password,password',
                'nullable',
                'string',
                'max:64',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap orang tua wajib diisi.',
            'nama.string' => 'Nama lengkap harus berupa teks yang valid.',
            'nama.min' => 'Nama lengkap minimal 2 karakter.',
            'nama.max' => 'Nama lengkap maksimal 60 karakter.',
            'nama.regex' => 'Nama lengkap hanya boleh berisi huruf dan spasi.',

            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar pada akun orang tua lain.',

            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks yang valid.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',

            'no_hp.required' => 'No. HP / WhatsApp wajib diisi.',
            'no_hp.digits_between' => 'No. HP / WhatsApp harus terdiri dari 8 sampai 13 digit angka.',

            'foto_profil.image' => 'Foto profil harus berupa berkas gambar.',
            'foto_profil.mimes' => 'Format foto profil harus JPG, JPEG, PNG, atau WEBP.',
            'foto_profil.max' => 'Ukuran foto profil maksimal 2 MB.',
            'foto_profil.uploaded' => 'Foto profil gagal diunggah. Pastikan ukuran berkas maksimal 2 MB.',

            'hapus_foto.boolean' => 'Status penghapusan foto tidak valid.',

            'current_password.required_with' => 'Kata sandi saat ini wajib diisi jika ingin mengubah kata sandi.',
            'current_password.string' => 'Kata sandi saat ini harus berupa teks yang valid.',
            'current_password.max' => 'Kata sandi saat ini maksimal 72 karakter.',

            'password.required_with' => 'Kata sandi baru wajib diisi jika ingin mengubah kata sandi.',
            'password.string' => 'Kata sandi baru harus berupa teks yang valid.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.max' => 'Kata sandi baru maksimal 64 karakter.',
            'password.different' => 'Kata sandi baru harus berbeda dengan kata sandi saat ini.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',

            'password_confirmation.required_with' => 'Konfirmasi kata sandi baru wajib diisi.',
            'password_confirmation.string' => 'Konfirmasi kata sandi baru harus berupa teks yang valid.',
            'password_confirmation.max' => 'Konfirmasi kata sandi baru maksimal 64 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if (is_string($this->input('nama'))) {
            $normalized['nama'] = preg_replace('/\s+/', ' ', trim($this->input('nama'))) ?? '';
        }

        if (is_string($this->input('nik'))) {
            $normalized['nik'] = trim($this->input('nik'));
        }

        if (is_string($this->input('email'))) {
            $normalized['email'] = strtolower(trim($this->input('email')));
        }

        if (is_string($this->input('no_hp'))) {
            $normalized['no_hp'] = trim($this->input('no_hp'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
