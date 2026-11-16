<?php

namespace Database\Seeders;

use App\Models\JenisVitamin;
use Illuminate\Database\Seeder;

class JenisVitaminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vitaminList = [
            [
                'nama_vitamin' => 'Vitamin A Biru (100.000 IU)',
                'deskripsi' => 'Diberikan untuk bayi usia 6-11 bulan untuk kesehatan mata dan daya tahan tubuh.',
            ],
            [
                'nama_vitamin' => 'Vitamin A Merah (200.000 IU)',
                'deskripsi' => 'Diberikan untuk balita usia 12-59 bulan serta ibu nifas.',
            ],
            [
                'nama_vitamin' => 'Obat Cacing (Albendazole 400mg)',
                'deskripsi' => 'Diberikan untuk balita usia 12-59 bulan setiap 6 bulan sekali.',
            ],
        ];

        foreach ($vitaminList as $vitamin) {
            JenisVitamin::firstOrCreate(
                ['nama_vitamin' => $vitamin['nama_vitamin']],
                ['deskripsi' => $vitamin['deskripsi']]
            );
        }
    }
}

