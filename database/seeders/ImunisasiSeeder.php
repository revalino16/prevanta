<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ImunisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(JenisImunisasiSeeder::class);
    }
}

