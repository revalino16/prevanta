<?php

namespace Database\Seeders;

use App\Models\JenisImunisasi;
use App\Models\JenisVitamin;
use App\Models\Users;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Users::firstOrCreate(
            ['email' => 'kader@prevanta.id'],
            [
                'nama' => 'Ibu Kader Mawar',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567890',
                'role' => 'kader',
            ]
        );

        $imunisasiList = [
            ['nama_imunisasi' => 'Hepatitis B (HB-0)', 'deskripsi' => 'Diberikan pada usia 0-7 hari untuk mencegah Hepatitis B.'],
            ['nama_imunisasi' => 'BCG', 'deskripsi' => 'Diberikan pada usia 1 bulan untuk mencegah Tuberkulosis (TBC).'],
            ['nama_imunisasi' => 'Polio Tetes 1 (OPV 1)', 'deskripsi' => 'Diberikan pada usia 1 bulan untuk mencegah Polio.'],
            ['nama_imunisasi' => 'DPT-HB-Hib 1', 'deskripsi' => 'Diberikan pada usia 2 bulan untuk mencegah Difteri, Pertusis, Tetanus, Hepatitis B, Pneumonia, dan Meningitis.'],
            ['nama_imunisasi' => 'Polio Tetes 2 (OPV 2)', 'deskripsi' => 'Diberikan pada usia 2 bulan.'],
            ['nama_imunisasi' => 'Rotavirus (RV) 1', 'deskripsi' => 'Diberikan pada usia 2 bulan untuk mencegah Diare berat akibat Rotavirus.'],
            ['nama_imunisasi' => 'PCV 1', 'deskripsi' => 'Diberikan pada usia 2 bulan untuk mencegah Pneumonia.'],
            ['nama_imunisasi' => 'DPT-HB-Hib 2', 'deskripsi' => 'Diberikan pada usia 3 bulan.'],
            ['nama_imunisasi' => 'Polio Tetes 3 (OPV 3)', 'deskripsi' => 'Diberikan pada usia 3 bulan.'],
            ['nama_imunisasi' => 'Rotavirus (RV) 2', 'deskripsi' => 'Diberikan pada usia 3 bulan.'],
            ['nama_imunisasi' => 'PCV 2', 'deskripsi' => 'Diberikan pada usia 3 bulan.'],
            ['nama_imunisasi' => 'DPT-HB-Hib 3', 'deskripsi' => 'Diberikan pada usia 4 bulan.'],
            ['nama_imunisasi' => 'Polio Tetes 4 (OPV 4)', 'deskripsi' => 'Diberikan pada usia 4 bulan.'],
            ['nama_imunisasi' => 'Polio Suntik 1 (IPV 1)', 'deskripsi' => 'Diberikan pada usia 4 bulan.'],
            ['nama_imunisasi' => 'Rotavirus (RV) 3', 'deskripsi' => 'Diberikan pada usia 4 bulan.'],
            ['nama_imunisasi' => 'Campak Rubella (MR 1)', 'deskripsi' => 'Diberikan pada usia 9 bulan untuk mencegah Campak dan Rubella.'],
            ['nama_imunisasi' => 'Polio Suntik 2 (IPV 2)', 'deskripsi' => 'Diberikan pada usia 9 bulan.'],
            ['nama_imunisasi' => 'PCV 3 (Booster)', 'deskripsi' => 'Diberikan pada usia 12 bulan.'],
            ['nama_imunisasi' => 'DPT-HB-Hib Lanjutan (Booster)', 'deskripsi' => 'Diberikan pada usia 18 bulan.'],
            ['nama_imunisasi' => 'Campak Rubella Lanjutan (MR 2)', 'deskripsi' => 'Diberikan pada usia 18 bulan.'],
        ];

        foreach ($imunisasiList as $imunisasi) {
            JenisImunisasi::firstOrCreate(
                ['nama_imunisasi' => $imunisasi['nama_imunisasi']],
                ['deskripsi' => $imunisasi['deskripsi']]
            );
        }

        $vitaminList = [
            ['nama_vitamin' => 'Vitamin A Kapsul Biru (100.000 IU)', 'deskripsi' => 'Diberikan untuk bayi usia 6 - 11 bulan pada bulan Februari dan Agustus.'],
            ['nama_vitamin' => 'Vitamin A Kapsul Merah (200.000 IU)', 'deskripsi' => 'Diberikan untuk balita usia 12 - 59 bulan pada bulan Februari dan Agustus.'],
            ['nama_vitamin' => 'Obat Cacing (Albendazole 400mg)', 'deskripsi' => 'Diberikan untuk balita usia 12 - 59 bulan setiap 6 bulan sekali.'],
        ];

        foreach ($vitaminList as $vitamin) {
            JenisVitamin::firstOrCreate(
                ['nama_vitamin' => $vitamin['nama_vitamin']],
                ['deskripsi' => $vitamin['deskripsi']]
            );
        }
    }
}
