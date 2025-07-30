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
    // Nonaktifkan foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // Kosongkan tabel menggunakan delete
    DB::table('akuns')->delete();

    // Isi ulang data
    DB::table('akuns')->insert([
        ['akun_id' => 1, 'kode_akun' => '1', 'nama_akun' => 'ASET', 'tipe_akun' => 'Aset', 'is_header' => 1, 'parent_id' => null],
        ['akun_id' => 2, 'kode_akun' => '1-10000', 'nama_akun' => 'Aset Lancar', 'tipe_akun' => 'Aset', 'is_header' => 1, 'parent_id' => 1],
        ['akun_id' => 3, 'kode_akun' => '1-10101', 'nama_akun' => 'Kas di Tangan', 'tipe_akun' => 'Kas & Bank', 'is_header' => 0, 'parent_id' => 2],
        ['akun_id' => 4, 'kode_akun' => '1-10200', 'nama_akun' => 'Piutang Usaha', 'tipe_akun' => 'Piutang', 'is_header' => 0, 'parent_id' => 2],
        ['akun_id' => 5, 'kode_akun' => '1-10400', 'nama_akun' => 'Persediaan Barang Dagang', 'tipe_akun' => 'Persediaan', 'is_header' => 0, 'parent_id' => 2],
        ['akun_id' => 6, 'kode_akun' => '2', 'nama_akun' => 'KEWAJIBAN', 'tipe_akun' => 'Liabilitas', 'is_header' => 1, 'parent_id' => null],
        ['akun_id' => 7, 'kode_akun' => '2-10100', 'nama_akun' => 'Utang Usaha', 'tipe_akun' => 'Utang', 'is_header' => 0, 'parent_id' => 6],
        ['akun_id' => 8, 'kode_akun' => '3', 'nama_akun' => 'EKUITAS', 'tipe_akun' => 'Ekuitas', 'is_header' => 1, 'parent_id' => null],
        ['akun_id' => 9, 'kode_akun' => '3-10100', 'nama_akun' => 'Modal Disetor', 'tipe_akun' => 'Ekuitas', 'is_header' => 0, 'parent_id' => 8],
        ['akun_id' => 10, 'kode_akun' => '4', 'nama_akun' => 'PENDAPATAN', 'tipe_akun' => 'Pendapatan', 'is_header' => 1, 'parent_id' => null],
        ['akun_id' => 11, 'kode_akun' => '4-10100', 'nama_akun' => 'Pendapatan Usaha', 'tipe_akun' => 'Pendapatan', 'is_header' => 0, 'parent_id' => 10],
        ['akun_id' => 12, 'kode_akun' => '5', 'nama_akun' => 'BEBAN', 'tipe_akun' => 'Beban', 'is_header' => 1, 'parent_id' => null],
        ['akun_id' => 13, 'kode_akun' => '5-10100', 'nama_akun' => 'Beban Pokok Penjualan', 'tipe_akun' => 'Beban', 'is_header' => 0, 'parent_id' => 12],
        ['akun_id' => 14, 'kode_akun' => '5-10200', 'nama_akun' => 'Beban Operasional', 'tipe_akun' => 'Beban', 'is_header' => 0, 'parent_id' => 12],
    ]);

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}

}
