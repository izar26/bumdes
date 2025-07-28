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
            ['kode_akun' => '1-10000', 'nama_akun' => 'Aset Lancar', 'tipe_akun' => 'Aset', 'is_header' => 1, 'parent_id' => 1],
            ['kode_akun' => '1-10100', 'nama_akun' => 'Kas dan Setara Kas', 'tipe_akun' => 'Aset', 'is_header' => 1, 'parent_id' => 2],
            ['kode_akun' => '1-10101', 'nama_akun' => 'Kas di Tangan', 'tipe_akun' => 'Kas & Bank', 'is_header' => 0, 'parent_id' => 3],
            ['kode_akun' => '1-10102', 'nama_akun' => 'Kas di Bank BRI', 'tipe_akun' => 'Kas & Bank', 'is_header' => 0, 'parent_id' => 3],
            ['kode_akun' => '1-10103', 'nama_akun' => 'Kas di Bank Mandiri', 'tipe_akun' => 'Kas & Bank', 'is_header' => 0, 'parent_id' => 3],
            ['kode_akun' => '1-10200', 'nama_akun' => 'Piutang Usaha', 'tipe_akun' => 'Piutang', 'is_header' => 0, 'parent_id' => 2],
            ['kode_akun' => '1-10300', 'nama_akun' => 'Piutang Pinjaman Anggota', 'tipe_akun' => 'Piutang', 'is_header' => 0, 'parent_id' => 2],
            ['kode_akun' => '1-10400', 'nama_akun' => 'Persediaan Barang Dagang', 'tipe_akun' => 'Persediaan', 'is_header' => 0, 'parent_id' => 2],

            ['kode_akun' => '1-20000', 'nama_akun' => 'Aset Tetap', 'tipe_akun' => 'Aset', 'is_header' => 1, 'parent_id' => 1],
            ['kode_akun' => '1-20100', 'nama_akun' => 'Tanah', 'tipe_akun' => 'Aset Tetap', 'is_header' => 0, 'parent_id' => 10],
            ['kode_akun' => '1-20200', 'nama_akun' => 'Bangunan', 'tipe_akun' => 'Aset Tetap', 'is_header' => 0, 'parent_id' => 10],
            ['kode_akun' => '1-20300', 'nama_akun' => 'Kendaraan', 'tipe_akun' => 'Aset Tetap', 'is_header' => 0, 'parent_id' => 10],
            ['kode_akun' => '1-20400', 'nama_akun' => 'Peralatan Kantor', 'tipe_akun' => 'Aset Tetap', 'is_header' => 0, 'parent_id' => 10],

            // ================= KEWAJIBAN =================
            ['kode_akun' => '2', 'nama_akun' => 'KEWAJIBAN', 'tipe_akun' => 'Liabilitas', 'is_header' => 1, 'parent_id' => null],
            ['kode_akun' => '2-10000', 'nama_akun' => 'Utang Jangka Pendek', 'tipe_akun' => 'Liabilitas', 'is_header' => 1, 'parent_id' => 15],
            ['kode_akun' => '2-10100', 'nama_akun' => 'Utang Usaha', 'tipe_akun' => 'Utang', 'is_header' => 0, 'parent_id' => 16],

            // ================= EKUITAS =================
            ['kode_akun' => '3', 'nama_akun' => 'EKUITAS', 'tipe_akun' => 'Ekuitas', 'is_header' => 1, 'parent_id' => null],
            ['kode_akun' => '3-10100', 'nama_akun' => 'Modal Disetor Desa', 'tipe_akun' => 'Ekuitas', 'is_header' => 0, 'parent_id' => 18],
            ['kode_akun' => '3-10200', 'nama_akun' => 'Simpanan Pokok Anggota', 'tipe_akun' => 'Ekuitas', 'is_header' => 0, 'parent_id' => 18],

            // ================= PENDAPATAN =================
            ['kode_akun' => '4', 'nama_akun' => 'PENDAPATAN', 'tipe_akun' => 'Pendapatan', 'is_header' => 1, 'parent_id' => null],
            ['kode_akun' => '4-10100', 'nama_akun' => 'Pendapatan Usaha Toko', 'tipe_akun' => 'Pendapatan', 'is_header' => 0, 'parent_id' => 21],
            ['kode_akun' => '4-10200', 'nama_akun' => 'Pendapatan Jasa Simpan Pinjam (Bunga)', 'tipe_akun' => 'Pendapatan', 'is_header' => 0, 'parent_id' => 21],
            ['kode_akun' => '4-10300', 'nama_akun' => 'Pendapatan Jasa Lainnya', 'tipe_akun' => 'Pendapatan', 'is_header' => 0, 'parent_id' => 21],

            // ================= BEBAN =================
            ['kode_akun' => '5', 'nama_akun' => 'BEBAN', 'tipe_akun' => 'Beban', 'is_header' => 1, 'parent_id' => null],
            ['kode_akun' => '5-10100', 'nama_akun' => 'Beban Gaji Karyawan', 'tipe_akun' => 'Beban', 'is_header' => 0, 'parent_id' => 25],
            ['kode_akun' => '5-10200', 'nama_akun' => 'Beban Listrik, Air, & Telepon', 'tipe_akun' => 'Beban', 'is_header' => 0, 'parent_id' => 25],
            ['kode_akun' => '5-10300', 'nama_akun' => 'Beban Alat Tulis Kantor (ATK)', 'tipe_akun' => 'Beban', 'is_header' => 0, 'parent_id' => 25],
            ['kode_akun' => '5-10400', 'nama_akun' => 'Beban Transportasi', 'tipe_akun' => 'Beban', 'is_header' => 0, 'parent_id' => 25],
            ['kode_akun' => '5-10500', 'nama_akun' => 'Beban Penyusutan Peralatan', 'tipe_akun' => 'Beban', 'is_header' => 0, 'parent_id' => 25],
        ]);
    }
}