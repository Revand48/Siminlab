<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'artrevand@gmail.com'], // Cek email unik
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
            ]
        );

        // Tambahkan contoh siswa jika perlu
        User::updateOrCreate(
            ['email' => 'artrevand2@gmail.com'],
            [
                'name' => 'Siswa',
                'password' => Hash::make('password123'),
                'role' => 'Siswa',
            ]
        );
    }
}
