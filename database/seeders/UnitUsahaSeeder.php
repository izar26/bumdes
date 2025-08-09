<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Schema;

class UnitUsahaSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        UnitUsaha::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Cari user yang relevan dari database dengan USERNAME YANG BENAR
        $manajerUnitUsaha = User::where('username', 'manajer_unit_usaha')->first();
        $adminUnitUsaha = User::where('username', 'admin_unit_usaha')->first();

        // Pastikan user ditemukan sebelum melanjutkan
        if (!$manajerUnitUsaha || !$adminUnitUsaha) {
            $this->command->error('User "manajer_unit_usaha" or "admin_unit_usaha" not found. Please run UserSeeder first.');
            return;
        }

        // 2. Buat unit usaha dengan user_id yang benar
        DB::table('unit_usahas')->insert([
            [
                'nama_unit' => 'Toko BUMDes',
                'jenis_usaha' => 'Perdagangan',
                'tanggal_mulai_operasi' => '2022-01-15',
                'status_operasi' => 'Aktif',
                'user_id' => $adminUnitUsaha->user_id, // Gunakan ID yang ditemukan
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_unit' => 'Wisata Desa',
                'jenis_usaha' => 'Pariwisata',
                'tanggal_mulai_operasi' => '2024-05-20',
                'status_operasi' => 'Aktif',
                'user_id' => $manajerUnitUsaha->user_id, // Gunakan ID yang ditemukan
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
