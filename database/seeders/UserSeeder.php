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
             'name'          => 'Admin BUMDes',
             'password' => Hash::make('password'),
             'email'         => 'example@gmail.com',
            'role'         => 'admin',
            'is_active'     => true,
            'last_login'    => now(),
        ]);
    }
}
