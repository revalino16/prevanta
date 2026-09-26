<?php

namespace Database\Seeders;

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
                'nama'     => 'Ibu Kader Mawar',
                'password' => Hash::make('password123'),
                'no_hp'    => '081234567890',
                'role'     => 'kader',
            ]
        );
    }
}
