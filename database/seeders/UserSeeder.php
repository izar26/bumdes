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
         User::firstOrCreate([
            'username'      => 'admin',
            'password' => Hash::make('password'),
            'role'         => 'admin',
            'is_active'     => true,
            'last_login'    => now(),
        ]);
    }
}
