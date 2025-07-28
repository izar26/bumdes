<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitUsahaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('unit_usahas')->insert([
            [
                'nama_unit' => 'Toko BUMDes',
                'jenis_usaha' => 'Perdagangan',
                'bungdes_id' => 1, // Asumsi ID BUMDes adalah 1
                'tanggal_mulai_operasi' => '2022-01-15',
                'status_operasi' => 'Aktif',
                'user_id' => 1, // Asumsi ID user admin adalah 1
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_unit' => 'Penyewaan Alat Pertanian',
                'jenis_usaha' => 'Jasa',
                'bungdes_id' => 1,
                'tanggal_mulai_operasi' => '2023-03-01',
                'status_operasi' => 'Aktif',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_unit' => 'Wisata Desa',
                'jenis_usaha' => 'Pariwisata',
                'bungdes_id' => 1,
                'tanggal_mulai_operasi' => '2024-05-20',
                'status_operasi' => 'Aktif',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}