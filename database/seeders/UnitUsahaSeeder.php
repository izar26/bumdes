<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User; // Tambahkan ini
use App\Models\UnitUsaha; // Tambahkan ini
use Illuminate\Support\Facades\Schema; // Tambahkan ini

class UnitUsahaSeeder extends Seeder
{
    public function run(): void
    {
        // Nonaktifkan constraint untuk truncate
        Schema::disableForeignKeyConstraints();
        UnitUsaha::truncate();
        Schema::enableForeignKeyConstraints();

        // Temukan user yang relevan dari UserSeeder
        $manajerWisata = User::where('username', 'manajer_wisata')->first();
        $adminUnitUsaha = User::where('username', 'admin_unit_usaha')->first();

        DB::table('unit_usahas')->insert([
            [
                'nama_unit' => 'Toko BUMDes',
                'jenis_usaha' => 'Perdagangan',
                'tanggal_mulai_operasi' => '2022-01-15',
                'status_operasi' => 'Aktif',
                // Tetapkan admin_unit_usaha sebagai penanggung jawab Toko
                'user_id' => $adminUnitUsaha->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_unit' => 'Wisata Desa',
                'jenis_usaha' => 'Pariwisata',
                'tanggal_mulai_operasi' => '2024-05-20',
                'status_operasi' => 'Aktif',
                // Tetapkan manajer_wisata sebagai penanggung jawab unit Wisata
                'user_id' => $manajerWisata->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}