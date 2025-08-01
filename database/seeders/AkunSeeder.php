<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Akun; // Pastikan Anda memiliki model Akun yang sesuai
use Illuminate\Support\Facades\Log; // Import Log Facade untuk debugging

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Menonaktifkan pemeriksaan kunci asing sementara untuk menghindari masalah hierarki saat truncating/seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('akuns')->truncate(); // Kosongkan tabel sebelum seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $accounts = [
            // KELOMPOK AKUN UTAMA (Level 1)
            '1.00.00.00' => ['nama_akun' => 'ASET', 'tipe_akun' => 'Aset', 'is_header' => true],
            '1.1.00.00' => ['nama_akun' => 'ASET LANCAR', 'tipe_akun' => 'Aset', 'is_header' => true],
                '1.1.01.00' => ['nama_akun' => 'Kas & Setara Kas', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.01.01' => ['nama_akun' => 'Kas Tunai', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.01.02' => ['nama_akun' => 'Kas di Bank BSI', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.01.03' => ['nama_akun' => 'Kas di Bank Mandiri', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.01.04' => ['nama_akun' => 'Kas di Bank BRI', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.01.05' => ['nama_akun' => 'Kas di Bank BPD', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.01.98' => ['nama_akun' => 'Kas Kecil (Petty Cash)', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.02.00' => ['nama_akun' => 'Setara Kas', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.02.01' => ['nama_akun' => 'Deposito <= 3 bulan', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.02.99' => ['nama_akun' => 'Setara Kas Lainnya', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.03.00' => ['nama_akun' => 'Piutang Usaha', 'tipe_akun' => 'Aset', 'is_header' => true], // Mengganti ini menjadi header
                    '1.1.03.01' => ['nama_akun' => 'Piutang Usaha', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.03.02' => ['nama_akun' => 'Piutang kepada Pegawai', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.03.99' => ['nama_akun' => 'Piutang Lainnya', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.04.00' => ['nama_akun' => 'Penyisihan Piutang Tak Tertagih', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.04.01' => ['nama_akun' => 'Penyisihan Piutang Usaha Tak Tertagih', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.04.02' => ['nama_akun' => 'Penyisihan Piutang kepada Pegawai Tak Tertagih', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.04.99' => ['nama_akun' => 'Penyisihan Piutang Lainnya Tak Tertagih', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.05.00' => ['nama_akun' => 'Persediaan', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.05.01' => ['nama_akun' => 'Persediaan Barang Dagangan', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.05.02' => ['nama_akun' => 'Persediaan Bahan Baku', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.05.03' => ['nama_akun' => 'Persediaan Barang Dalam Proses', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.05.04' => ['nama_akun' => 'Persediaan Barang Jadi', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.06.00' => ['nama_akun' => 'Perlengkapan Kantor', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.06.01' => ['nama_akun' => 'Alat Tulis Kantor (ATK)', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.07.00' => ['nama_akun' => 'Beban Dibayar Dimuka', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.07.01' => ['nama_akun' => 'Sewa Dibayar Dimuka', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.07.02' => ['nama_akun' => 'Asuransi Dibayar Dimuka', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.07.03' => ['nama_akun' => 'PPh 25', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.07.04' => ['nama_akun' => 'PPN Masukan', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.98.00' => ['nama_akun' => 'Aset Lancar Lainnya', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.98.99' => ['nama_akun' => 'Aset Lancar Lainnya', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.1.99.00' => ['nama_akun' => 'Rekening Antar Unit Usaha', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.1.99.01' => ['nama_akun' => 'RK Unit Wisata', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.99.02' => ['nama_akun' => 'RK Unit Restoran', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.99.03' => ['nama_akun' => 'RK Unit Minimart Desa', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.99.04' => ['nama_akun' => 'RK Unit Gedung Serbaguna', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.99.05' => ['nama_akun' => 'RK Unit Simpan Pinjam', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.99.06' => ['nama_akun' => 'RK Unit Pengelolaan Air Bersih', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.1.99.07' => ['nama_akun' => 'RK Unit Pengelolaan Sampah', 'tipe_akun' => 'Aset', 'is_header' => false],
            '1.2.00.00' => ['nama_akun' => 'INVESTASI JANGKA PANJANG', 'tipe_akun' => 'Aset', 'is_header' => true],
                '1.2.01.00' => ['nama_akun' => 'Investasi', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.2.01.01' => ['nama_akun' => 'Deposito > 3 bulan', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.2.01.99' => ['nama_akun' => 'Investasi Lainnya', 'tipe_akun' => 'Aset', 'is_header' => false],
            '1.3.00.00' => ['nama_akun' => 'ASET TETAP', 'tipe_akun' => 'Aset', 'is_header' => true],
                '1.3.01.00' => ['nama_akun' => 'Tanah', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.01.01' => ['nama_akun' => 'Tanah', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.3.02.00' => ['nama_akun' => 'Kendaraan', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.02.01' => ['nama_akun' => 'Kendaraan', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.3.03.00' => ['nama_akun' => 'Peralatan dan Mesin', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.03.01' => ['nama_akun' => 'Peralatan dan Mesin', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.3.04.00' => ['nama_akun' => 'Meubelair', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.04.01' => ['nama_akun' => 'Meubelair', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.3.05.00' => ['nama_akun' => 'Gedung dan Bangunan', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.05.01' => ['nama_akun' => 'Gedung dan Bangunan', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.3.06.00' => ['nama_akun' => 'Konstruksi Dalam Pengerjaan', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.06.01' => ['nama_akun' => 'Konstruksi Dalam Pengerjaan', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.3.07.00' => ['nama_akun' => 'Akumulasi Penyusutan Aset Tetap', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.07.01' => ['nama_akun' => 'Akumulasi Penyusutan Kendaraan', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.3.07.02' => ['nama_akun' => 'Akumulasi Penyusutan Peralatan dan Mesin', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.3.07.03' => ['nama_akun' => 'Akumulasi Penyusutan Meubelair', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.3.07.04' => ['nama_akun' => 'Akumulasi Penyusutan Gedung dan Bangunan', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.3.99.00' => ['nama_akun' => 'Aset Tetap Lainnya', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.3.99.99' => ['nama_akun' => 'Aset Tetap Lainnya', 'tipe_akun' => 'Aset', 'is_header' => false],
            '1.4.00.00' => ['nama_akun' => 'ASET TAK BERWUJUD', 'tipe_akun' => 'Aset', 'is_header' => true],
                '1.4.01.00' => ['nama_akun' => 'Aset Tak Berwujud', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.4.01.01' => ['nama_akun' => 'Software', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.4.01.02' => ['nama_akun' => 'Patent', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.4.01.03' => ['nama_akun' => 'Trademark', 'tipe_akun' => 'Aset', 'is_header' => false],
                '1.4.02.00' => ['nama_akun' => 'Amortisasi Aset Tak Berwujud', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.4.02.01' => ['nama_akun' => 'Amortisasi Aset takberwujud', 'tipe_akun' => 'Aset', 'is_header' => false],
            '1.9.00.00' => ['nama_akun' => 'ASET LAIN-LAIN', 'tipe_akun' => 'Aset', 'is_header' => true],
                '1.9.01.00' => ['nama_akun' => 'Aset Lain-lain', 'tipe_akun' => 'Aset', 'is_header' => true],
                    '1.9.01.01' => ['nama_akun' => 'Aset Lain-lain', 'tipe_akun' => 'Aset', 'is_header' => false],
                    '1.9.01.02' => ['nama_akun' => 'Akumulasi Penyusutan Aset Lain-lain', 'tipe_akun' => 'Aset', 'is_header' => false],

            '2.00.00.00' => ['nama_akun' => 'KEWAJIBAN', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                '2.1.00.00' => ['nama_akun' => 'KEWAJIBAN JANGKA PENDEK', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                    '2.1.01.00' => ['nama_akun' => 'Utang Usaha', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.1.01.01' => ['nama_akun' => 'Utang Usaha', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                    '2.1.02.00' => ['nama_akun' => 'Utang Pajak', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.1.02.01' => ['nama_akun' => 'PPN Keluaran', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                        '2.1.02.02' => ['nama_akun' => 'PPh 21', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                        '2.1.02.03' => ['nama_akun' => 'PPh 23', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                        '2.1.02.04' => ['nama_akun' => 'PPh 29', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                    '2.1.03.00' => ['nama_akun' => 'Utang Gaji & Tunjangan', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.1.03.01' => ['nama_akun' => 'Utang Gaji dan Tunjangan', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                        '2.1.03.02' => ['nama_akun' => 'Utang Gaji/Upah Karyawan', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                    '2.1.04.00' => ['nama_akun' => 'Utang Utilitas', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.1.04.01' => ['nama_akun' => 'Utang Listrik', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                        '2.1.04.02' => ['nama_akun' => 'Utang Telepon/Internet', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                        '2.1.04.93' => ['nama_akun' => 'Utang Utilitas Lainnya', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                    '2.1.05.00' => ['nama_akun' => 'Utang Pihak Ketiga Jangka Pendek', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.1.05.01' => ['nama_akun' => 'Utang kepada Pihak Ketiga Jk. Pendek', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                        '2.1.05.99' => ['nama_akun' => 'Utang kepada Pihak Ketiga Jk. Pendek Lainnya', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                    '2.1.09.00' => ['nama_akun' => 'Utang Jangka Pendek Lainnya', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.1.09.99' => ['nama_akun' => 'Utang Jangka Pendek Lainnya', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                '2.2.00.00' => ['nama_akun' => 'KEWAJIBAN JANGKA PANJANG', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                    '2.2.01.00' => ['nama_akun' => 'Utang Bank', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.2.01.01' => ['nama_akun' => 'Utang Ke Bank', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                    '2.2.02.00' => ['nama_akun' => 'Utang Pihak Ketiga Jangka Panjang', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.2.02.01' => ['nama_akun' => 'Utang kepada Pihak Ketiga Jk. Panjang', 'tipe_akun' => 'Kewajiban', 'is_header' => false],
                    '2.2.99.00' => ['nama_akun' => 'Utang Jangka Panjang Lainnya', 'tipe_akun' => 'Kewajiban', 'is_header' => true],
                        '2.2.99.99' => ['nama_akun' => 'Utang Jangka Panjang Lainnya', 'tipe_akun' => 'Kewajiban', 'is_header' => false],

            '3.00.00.00' => ['nama_akun' => 'EKUITAS', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                '3.1.00.00' => ['nama_akun' => 'Modal', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                    '3.1.01.00' => ['nama_akun' => 'Penyertaan Modal Desa', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                        '3.1.01.01' => ['nama_akun' => 'Penyertaan Modal Desa', 'tipe_akun' => 'Ekuitas', 'is_header' => false], // Diperbaiki agar akun induknya tidak sama namanya
                        '3.1.01.02' => ['nama_akun' => 'Penyertaan Modal Desa A', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.1.01.03' => ['nama_akun' => 'Penyertaan Modal Desa B', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.1.01.04' => ['nama_akun' => 'Penyertaan Modal Desa C', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                    '3.1.02.00' => ['nama_akun' => 'Penyertaan Modal Masyarakat', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                        '3.1.02.01' => ['nama_akun' => 'Penyertaan Modal Masyarakat', 'tipe_akun' => 'Ekuitas', 'is_header' => false], // Diperbaiki
                        '3.1.02.02' => ['nama_akun' => 'Penyertaan Modal Masyarakat Desa A', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.1.02.03' => ['nama_akun' => 'Penyertaan Modal Masyarakat Desa B', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.1.02.04' => ['nama_akun' => 'Penyertaan Modal Masyarakat Desa C', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                    '3.1.03.00' => ['nama_akun' => 'Modal Donasi/Sumbangan', 'tipe_akun' => 'Ekuitas', 'is_header' => true], // Dipindahkan dan diperbaiki
                        '3.4.01.01' => ['nama_akun' => 'Modal Donasi/Sumbangan', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                '3.2.00.00' => ['nama_akun' => 'Bagian Laba Bersih', 'tipe_akun' => 'Ekuitas', 'is_header' => true], // Dibuat header baru
                    '3.2.01.00' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Desa', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                        '3.2.01.01' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Desa', 'tipe_akun' => 'Ekuitas', 'is_header' => false], // Diperbaiki
                        '3.2.01.02' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Desa A', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.2.01.03' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Desa B', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.2.01.04' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Desa C', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                    '3.2.02.00' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Masyarakat', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                        '3.2.02.01' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Masyarakat', 'tipe_akun' => 'Ekuitas', 'is_header' => false], // Diperbaiki
                        '3.2.02.02' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Masyarakat Desa A', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.2.02.03' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Masyarakat Desa B', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        '3.2.02.04' => ['nama_akun' => 'Bagi Hasil Penyertaan Modal Masyarakat Desa C', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                '3.3.00.00' => ['nama_akun' => 'Laba Ditahan', 'tipe_akun' => 'Ekuitas', 'is_header' => true], // Dibuat header baru
                    '3.3.01.00' => ['nama_akun' => 'Saldo Laba Tidak Dicadangkan', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                        '3.3.01.01' => ['nama_akun' => 'Saldo Laba Tidak Dicadangkan', 'tipe_akun' => 'Ekuitas', 'is_header' => false], // Diperbaiki
                    '3.3.02.00' => ['nama_akun' => 'Saldo Laba Dicadangkan', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                        '3.3.02.01' => ['nama_akun' => 'Saldo Laba Dicadangkan untuk Pembelian Aset Tetap', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
                        // PERBAIKAN: Kode akun 3.3.01.02 diubah menjadi 3.3.02.02 agar menjadi anak dari 'Saldo Laba Dicadangkan'
                        '3.3.02.02' => ['nama_akun' => 'Saldo Laba Dicadangkan untuk Pembayaran Utang Jangka Panjang', 'tipe_akun' => 'Ekuitas', 'is_header' => false],

            '3.8.00.00' => ['nama_akun' => 'Rekening Antar Pusat', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                '3.8.01.01' => ['nama_akun' => 'RK Pusat', 'tipe_akun' => 'Ekuitas', 'is_header' => false],
            '3.9.00.00' => ['nama_akun' => 'Ikhtisar Laba Rugi', 'tipe_akun' => 'Ekuitas', 'is_header' => true],
                '3.9.01.01' => ['nama_akun' => 'Ikhtisar Laba Rugi', 'tipe_akun' => 'Ekuitas', 'is_header' => false],


            '4.00.00.00' => ['nama_akun' => 'PENDAPATAN', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                '4.1.00.00' => ['nama_akun' => 'PENDAPATAN JASA', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                    '4.1.01.00' => ['nama_akun' => 'Pendapatan Unit Wisata', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.01.01' => ['nama_akun' => 'Pendapatan Tiket', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.01.02' => ['nama_akun' => 'Pendapatan Wahana', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.01.03' => ['nama_akun' => 'Pendapatan Paket Wisata', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.02.00' => ['nama_akun' => 'Pendapatan Pengelolaan Air Bersih', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.02.01' => ['nama_akun' => 'Pendapatan Pengelolaan Air Bersih', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.03.00' => ['nama_akun' => 'Pendapatan Pengelolaan Sampah', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.03.01' => ['nama_akun' => 'Pendapatan Pengelolaan Sampah', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.04.00' => ['nama_akun' => 'Pendapatan Sewa', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.04.01' => ['nama_akun' => 'Pendapatan Sewa Tempat Outbound', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.04.02' => ['nama_akun' => 'Pendapatan Sewa Tempat untuk Toko/Kios', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.04.03' => ['nama_akun' => 'Pendapatan Sewa Gedung', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.04.04' => ['nama_akun' => 'Pendapatan Sewa Mobil', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.04.05' => ['nama_akun' => 'Pendapatan Sewa Peralatan Gedung', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.04.99' => ['nama_akun' => 'Pendapatan Sewa Lainnya', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.05.00' => ['nama_akun' => 'Pendapatan Jasa Pelayanan', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.05.01' => ['nama_akun' => 'Pendapatan Jasa Pembayaran Listrik', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.05.99' => ['nama_akun' => 'Pendapatan Jasa Pelayanan lainnya', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.06.00' => ['nama_akun' => 'Pendapatan Transportasi', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.06.01' => ['nama_akun' => 'Pendapatan Transportasi', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.07.00' => ['nama_akun' => 'Pendapatan Parkir', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.07.01' => ['nama_akun' => 'Pendapatan Parkir Mobil', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.1.07.02' => ['nama_akun' => 'Pendapatan Parkir Motor', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.08.00' => ['nama_akun' => 'Pendapatan Simpan Pinjam', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.08.01' => ['nama_akun' => 'Pendapatan Simpan Pinjam', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.09.00' => ['nama_akun' => 'Pendapatan Pelatihan', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.09.01' => ['nama_akun' => 'Pendapatan Pelatihan', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.10.00' => ['nama_akun' => 'Pendapatan Homestay', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.10.01' => ['nama_akun' => 'Pendapatan Homestay', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.11.00' => ['nama_akun' => 'Pendapatan Komisi', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.11.01' => ['nama_akun' => 'Pendapatan Komisi', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.1.12.00' => ['nama_akun' => 'Pendapatan BRI Link', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.1.12.01' => ['nama_akun' => 'Pendapatan BRI Link', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                '4.2.00.00' => ['nama_akun' => 'PENDAPATAN PENJUALAN BARANG DAGANGAN', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                    '4.2.01.00' => ['nama_akun' => 'Pendapatan Penjualan', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.2.01.01' => ['nama_akun' => 'Pendapatan Penjualan Makanan/Minuman', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.2.01.02' => ['nama_akun' => 'Pendapatan Penjualan Pakaian/Kaos/Jaket', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.2.01.03' => ['nama_akun' => 'Pendapatan Penjualan Hasil Kerajinan/Suvenir', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.2.01.04' => ['nama_akun' => 'Pendapatan Penjualan Buku', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.2.01.05' => ['nama_akun' => 'Pendapatan Penjualan Biji Kopi', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.2.01.06' => ['nama_akun' => 'Pendapatan Penjualan Bensin', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.2.01.91' => ['nama_akun' => 'Pendapatan Penjualan Barang Dagangan', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.2.02.00' => ['nama_akun' => 'Retur Penjualan Barang Dagangan', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.2.02.01' => ['nama_akun' => 'Retur Penjualan Barang Dagangan', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.2.03.00' => ['nama_akun' => 'Diskon Penjualan Barang Dagangan', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.2.03.01' => ['nama_akun' => 'Diskon Penjualan Barang Dagangan', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                '4.3.00.00' => ['nama_akun' => 'PENDAPATAN PENJUALAN BARANG JADI', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                    '4.3.01.00' => ['nama_akun' => 'Pendapatan Penjualan Barang Jadi', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.3.01.01' => ['nama_akun' => 'Pendapatan Katering', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.3.01.02' => ['nama_akun' => 'Pendapatan Restoran', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.3.01.03' => ['nama_akun' => 'Pendapatan Kopi', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                        '4.3.01.91' => ['nama_akun' => 'Pendapatan Penjualan Barang Jadi', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.3.02.00' => ['nama_akun' => 'Retur Penjualan Barang Jadi', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.3.02.01' => ['nama_akun' => 'Retur Penjualan Barang Jadi', 'tipe_akun' => 'Pendapatan', 'is_header' => false],
                    '4.3.03.00' => ['nama_akun' => 'Diskon Penjualan Barang Jadi', 'tipe_akun' => 'Pendapatan', 'is_header' => true],
                        '4.3.03.01' => ['nama_akun' => 'Diskon Penjualan Barang Jadi', 'tipe_akun' => 'Pendapatan', 'is_header' => false],


            '5.00.00.00' => ['nama_akun' => 'HARGA POKOK PENJUALAN', 'tipe_akun' => 'HPP', 'is_header' => true],
                '5.1.00.00' => ['nama_akun' => 'Harga Pokok Penjualan Barang Dagangan', 'tipe_akun' => 'HPP', 'is_header' => true],
                    '5.1.01.01' => ['nama_akun' => 'Harga Pokok Penjualan Barang Dagangan', 'tipe_akun' => 'HPP', 'is_header' => false],
                '5.2.00.00' => ['nama_akun' => 'Harga Pokok Penjualan Barang Jadi', 'tipe_akun' => 'HPP', 'is_header' => true],
                    '5.2.01.01' => ['nama_akun' => 'Harga Pokok Penjualan Barang Jadi', 'tipe_akun' => 'HPP', 'is_header' => false],
                '5.3.00.00' => ['nama_akun' => 'Harga Pokok Produksi', 'tipe_akun' => 'HPP', 'is_header' => true],
                    '5.3.01.01' => ['nama_akun' => 'Harga Pokok Produksi', 'tipe_akun' => 'HPP', 'is_header' => false],


            '6.00.00.00' => ['nama_akun' => 'BEBAN', 'tipe_akun' => 'Beban', 'is_header' => true],
                '6.1.00.00' => ['nama_akun' => 'BEBAN ADMINISTRASI & UMUM', 'tipe_akun' => 'Beban', 'is_header' => true],
                    '6.1.01.00' => ['nama_akun' => 'Beban Gaji & Tunjangan Adum', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.01.01' => ['nama_akun' => 'Beban Gaji dan Tunjangan Bag. Adum', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.01.02' => ['nama_akun' => 'Beban Honor Lembur Bag. Adum', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.01.03' => ['nama_akun' => 'Beban Honor Narasumber', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.01.04' => ['nama_akun' => 'Beban Insentif (Bonus) Bag. Adum', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.01.05' => ['nama_akun' => 'Beban Komisi Bag. Adum', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.01.06' => ['nama_akun' => 'Beban Seragam Pegawai Bag. Adum', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.01.07' => ['nama_akun' => 'Beban Penguatan SDM', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.01.99' => ['nama_akun' => 'Beban Pegawai Bag. Adum Lainnya', 'tipe_akun' => 'Beban', 'is_header' => false],
                    '6.1.02.00' => ['nama_akun' => 'Beban Perlengkapan', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.02.01' => ['nama_akun' => 'Beban Alat Tulis Kantor (ATK)', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.02.02' => ['nama_akun' => 'Beban Foto Copy', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.02.03' => ['nama_akun' => 'Beban Konsumsi Rapat', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.02.04' => ['nama_akun' => 'Beban Cetak dan Dekorasi', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.02.99' => ['nama_akun' => 'Beban Perlengkapan Lainnya', 'tipe_akun' => 'Beban', 'is_header' => false],
                    '6.1.03.00' => ['nama_akun' => 'Beban Pemeliharaan & Perbaikan', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.03.01' => ['nama_akun' => 'Beban Pemeliharaan dan Perbaikan', 'tipe_akun' => 'Beban', 'is_header' => false],
                    '6.1.04.00' => ['nama_akun' => 'Beban Utilitas', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.04.01' => ['nama_akun' => 'Beban Listrik', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.04.02' => ['nama_akun' => 'Beban Telepon/Internet', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.04.99' => ['nama_akun' => 'Beban Utilitas Lainnya', 'tipe_akun' => 'Beban', 'is_header' => false],
                    '6.1.05.00' => ['nama_akun' => 'Beban Sewa & Asuransi', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.05.01' => ['nama_akun' => 'Beban Sewa', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.05.02' => ['nama_akun' => 'Beban Asuransi', 'tipe_akun' => 'Beban', 'is_header' => false],
                    '6.1.06.00' => ['nama_akun' => 'Beban Kebersihan & Keamanan', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.06.01' => ['nama_akun' => 'Beban Kebersihan', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.06.02' => ['nama_akun' => 'Beban Keamanan', 'tipe_akun' => 'Beban', 'is_header' => false],
                    '6.1.07.00' => ['nama_akun' => 'Beban Penyusutan & Amortisasi', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.07.01' => ['nama_akun' => 'Beban Penyisihan Piutang Tak Tertagih', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.07.02' => ['nama_akun' => 'Beban Penyusutan Kendaraan', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.07.03' => ['nama_akun' => 'Beban Penyusutan Peralatan dan Mesin', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.07.04' => ['nama_akun' => 'Beban Penyusutan Meubelair', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.07.05' => ['nama_akun' => 'Beban Penyusutan Gedung dan Bangunan', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.07.06' => ['nama_akun' => 'Beban Amortisasi Aset takberwujud', 'tipe_akun' => 'Beban', 'is_header' => false],
                    '6.1.99.00' => ['nama_akun' => 'Beban Administrasi dan Umum Lainnya', 'tipe_akun' => 'Beban', 'is_header' => true],
                        '6.1.99.01' => ['nama_akun' => 'Beban Parkir', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.99.02' => ['nama_akun' => 'Beban Audit', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.99.03' => ['nama_akun' => 'Beban Perjalanan Dinas', 'tipe_akun' => 'Beban', 'is_header' => false],
                        '6.1.99.04' => ['nama_akun' => 'Beban Transportasi', 'tipe_akun' => 'Beban', 'is_header' => false],
        ];


        $insertedIds = []; // Untuk menyimpan ID akun yang sudah diinsert

        // Function untuk mendapatkan parent_id
        $getParentId = function ($kode_akun_child) use (&$insertedIds) {
            $parts = explode('.', $kode_akun_child);
            array_pop($parts); // Hapus bagian terakhir (child)
            while (count($parts) > 0) {
                $parent_kode = implode('.', $parts);
                if (isset($insertedIds[$parent_kode])) {
                    return $insertedIds[$parent_kode];
                }
                array_pop($parts); // Coba level yang lebih tinggi
            }
            return null; // Tidak ada parent
        };

        // Urutkan akun berdasarkan panjang kode akun (dari paling pendek/atas ke paling panjang/bawah)
        // Ini memastikan akun induk diinsert terlebih dahulu
        uksort($accounts, function($a, $b) {
            $lenA = count(explode('.', $a));
            $lenB = count(explode('.', $b));
            if ($lenA == $lenB) {
                return strcmp($a, $b); // Urutkan secara alfabetis jika panjang sama
            }
            return $lenA <=> $lenB;
        });


        foreach ($accounts as $kode_akun => $data) {
            $parentId = $getParentId($kode_akun);


            $akun = Akun::create([
                'kode_akun' => $kode_akun,
                'nama_akun' => $data['nama_akun'],
                'tipe_akun' => $data['tipe_akun'],
                'is_header' => $data['is_header'],
                'parent_id' => $parentId,
            ]);
            $insertedIds[$kode_akun] = $akun->akun_id; // Simpan ID akun yang baru diinsert
        }
    }
}
