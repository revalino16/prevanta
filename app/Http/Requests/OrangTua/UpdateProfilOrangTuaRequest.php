<?php

namespace App\Http\Requests\OrangTua;

use App\Models\Users;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
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
            'nama' => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.,\'\-]+$/'],
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
            'password' => ['bail', 'nullable', 'string', 'min:8', 'confirmed'],
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
            'nama.max' => 'Nama lengkap maksimal 100 karakter.',
            'nama.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, koma, tanda petik, atau tanda hubung.',

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

            'password.string' => 'Kata sandi baru harus berupa teks yang valid.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if (is_string($this->input('nama'))) {
            $normalized['nama'] = trim($this->input('nama'));
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