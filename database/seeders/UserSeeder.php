<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'], // Cari user dengan username 'admin'
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com', // Email bisa disesuaikan
                'password' => Hash::make('password'), // Password di-hash menjadi 'password'
            ]
        );
    }
}