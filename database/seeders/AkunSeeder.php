<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('akuns')->insert([
            // ================= ASET =================
            ['kode_akun' => '1', 'nama_akun' => 'ASET', 'tipe_akun' => 'Aset', 'is_header' => 1, 'parent_id' => null],
        ]);
    }
}
