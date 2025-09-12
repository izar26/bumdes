<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
{
    DB::table('tarifs')->insert([
        // Tarif Pemakaian Progresif
        ['jenis_tarif' => 'pemakaian', 'deskripsi' => 'Pemakaian Blok 1 (0-5 m³)', 'batas_bawah' => 0, 'batas_atas' => 5, 'harga' => 3000.00],
        ['jenis_tarif' => 'pemakaian', 'deskripsi' => 'Pemakaian Blok 2 (6-15 m³)', 'batas_bawah' => 6, 'batas_atas' => 15, 'harga' => 3350.00],
        ['jenis_tarif' => 'pemakaian', 'deskripsi' => 'Pemakaian Blok 3 (16-25 m³)', 'batas_bawah' => 16, 'batas_atas' => 25, 'harga' => 3750.00],
        ['jenis_tarif' => 'pemakaian', 'deskripsi' => 'Pemakaian Blok 4 (>25 m³)', 'batas_bawah' => 26, 'batas_atas' => null, 'harga' => 3999.00], // batas_atas null artinya tak terbatas

        // Biaya Tetap
        ['jenis_tarif' => 'biaya_tetap', 'deskripsi' => 'Biaya Pemeliharaan', 'batas_bawah' => null, 'batas_atas' => null, 'harga' => 4500.00],
        ['jenis_tarif' => 'biaya_tetap', 'deskripsi' => 'Biaya Administrasi', 'batas_bawah' => null, 'batas_atas' => null, 'harga' => 3500.00],

        // Denda (Contoh denda tetap)
        ['jenis_tarif' => 'denda', 'deskripsi' => 'Denda Keterlambatan', 'batas_bawah' => null, 'batas_atas' => null, 'harga' => 10000.00],
    ]);
}
}
