<?php

namespace App\Http\Requests;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Support\AnthropometricMeasurementLimits;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StorePengukuranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'kader';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Balita $balita */
        $balita = $this->route('balita');

        return [
            'tanggal_pengukuran' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$balita->tanggal_lahir->toDateString(),
                'before_or_equal:'.now('Asia/Jakarta')->toDateString(),
                'before_or_equal:'.$balita->tanggal_lahir->copy()->addYears(5)->toDateString(),
                function (string $attribute, mixed $value, \Closure $fail) use ($balita): void {
                    $date = Carbon::createFromFormat('Y-m-d', $value);

                    if ($date === false) {
                        return;
                    }

                    $exists = Pengukuran::query()
                        ->where('balita_id', $balita->getKey())
                        ->whereYear('tanggal_pengukuran', $date->year)
                        ->whereMonth('tanggal_pengukuran', $date->month)
                        ->exists();

                    if ($exists) {
                        $fail('Pengukuran balita pada bulan '.($date->locale('id')->translatedFormat('F Y')).' sudah pernah dicatat.');
                    }
                },
            ],
            'berat_badan' => ['bail', 'required', 'numeric', 'decimal:0,2', 'between:'.AnthropometricMeasurementLimits::WEIGHT_MIN_KG.','.AnthropometricMeasurementLimits::WEIGHT_MAX_KG],
            'tinggi_badan' => ['bail', 'required', 'numeric', 'decimal:0,2', 'between:'.AnthropometricMeasurementLimits::HEIGHT_MIN_CM.','.AnthropometricMeasurementLimits::HEIGHT_MAX_CM],
            'lingkar_lengan_atas' => ['bail', 'required', 'numeric', 'decimal:0,2', 'between:'.AnthropometricMeasurementLimits::ARM_CIRCUMFERENCE_MIN_CM.','.AnthropometricMeasurementLimits::ARM_CIRCUMFERENCE_MAX_CM],
            'lingkar_kepala' => ['bail', 'required', 'numeric', 'decimal:0,2', 'between:'.AnthropometricMeasurementLimits::HEAD_CIRCUMFERENCE_MIN_CM.','.AnthropometricMeasurementLimits::HEAD_CIRCUMFERENCE_MAX_CM],
            'foto_pertumbuhan' => [
                'bail',
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max('2mb')
                    ->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
            ],
            'jenis_imunisasi_id' => ['nullable', 'integer', 'exists:jenis_imunisasi,id'],
            'jenis_vitamin_id' => ['nullable', 'integer', 'exists:jenis_vitamin,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal_pengukuran.required' => 'Tanggal pengukuran wajib diisi.',
            'tanggal_pengukuran.date_format' => 'Format tanggal pengukuran tidak valid.',
            'tanggal_pengukuran.after_or_equal' => 'Tanggal pengukuran tidak boleh sebelum tanggal lahir balita.',
            'tanggal_pengukuran.before_or_equal' => 'Tanggal pengukuran tidak boleh melewati hari ini atau batas usia balita 5 tahun.',
            'berat_badan.required' => 'Berat badan wajib diisi.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.decimal' => 'Berat badan hanya boleh menggunakan maksimal 2 angka di belakang koma.',
            'berat_badan.between' => 'Berat badan harus berada antara 0,9 sampai 30 kg.',
            'tinggi_badan.required' => 'Panjang atau tinggi badan wajib diisi.',
            'tinggi_badan.numeric' => 'Panjang atau tinggi badan harus berupa angka.',
            'tinggi_badan.decimal' => 'Panjang atau tinggi badan hanya boleh menggunakan maksimal 2 angka di belakang koma.',
            'tinggi_badan.between' => 'Panjang atau tinggi badan harus berada antara 35 sampai 130 cm.',
            'lingkar_lengan_atas.required' => 'Lingkar lengan atas wajib diisi.',
            'lingkar_lengan_atas.numeric' => 'Lingkar lengan atas harus berupa angka.',
            'lingkar_lengan_atas.decimal' => 'Lingkar lengan atas hanya boleh menggunakan maksimal 2 angka di belakang koma.',
            'lingkar_lengan_atas.between' => 'Lingkar lengan atas harus berada antara 5 sampai 25 cm.',
            'lingkar_kepala.required' => 'Lingkar kepala wajib diisi.',
            'lingkar_kepala.numeric' => 'Lingkar kepala harus berupa angka.',
            'lingkar_kepala.decimal' => 'Lingkar kepala hanya boleh menggunakan maksimal 2 angka di belakang koma.',
            'lingkar_kepala.between' => 'Lingkar kepala harus berada antara 20 sampai 60 cm.',
            'foto_pertumbuhan.image' => 'Berkas dokumentasi harus berupa gambar.',
            'foto_pertumbuhan.mimes' => 'Berkas dokumentasi harus berupa gambar JPG, JPEG, PNG, atau WEBP.',
            'foto_pertumbuhan.max' => 'Ukuran gambar tidak boleh lebih dari 2 MB.',
            'foto_pertumbuhan.dimensions' => 'Ukuran sisi gambar tidak boleh lebih dari 4096 piksel.',
            'jenis_imunisasi_id.integer' => 'Pilihan imunisasi tidak valid.',
            'jenis_imunisasi_id.exists' => 'Jenis imunisasi yang dipilih tidak tersedia.',
            'jenis_vitamin_id.integer' => 'Pilihan vitamin tidak valid.',
            'jenis_vitamin_id.exists' => 'Jenis vitamin yang dipilih tidak tersedia.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $numericFields = [
            'berat_badan',
            'tinggi_badan',
            'lingkar_lengan_atas',
            'lingkar_kepala',
        ];
        $normalizedValues = [];

        foreach ($numericFields as $field) {
            if (is_string($this->input($field))) {
                $normalizedValues[$field] = str_replace(',', '.', trim($this->input($field)));
            }
        }

        $this->merge($normalizedValues);
    }
}
