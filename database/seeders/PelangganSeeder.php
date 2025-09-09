<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pelanggan')->insert([
            ['nama' => 'Budi Santoso', 'alamat' => 'Blok A No. 1', 'status_pelanggan' => 'Aktif'],
            ['nama' => 'Siti Aminah', 'alamat' => 'Blok B No. 5', 'status_pelanggan' => 'Aktif'],
            ['nama' => 'Joko Widodo', 'alamat' => 'Blok C No. 12', 'status_pelanggan' => 'Aktif'],
        ]);
    }
}
