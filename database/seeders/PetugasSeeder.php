<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        $petugas = [
            ['nama_petugas' => 'KOLEKTOR OBI'],
            ['nama_petugas' => 'KOLEKTOR DADANG'],
            ['nama_petugas' => 'KOLEKTOR DINDAN 1'],
            ['nama_petugas' => 'KOLEKTOR DINDAN'],
        ];

        DB::table('petugas')->insert($petugas);
    }
}
