<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VitaminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(JenisVitaminSeeder::class);
    }
}

