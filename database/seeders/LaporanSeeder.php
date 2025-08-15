<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UnitUsaha;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use Illuminate\Support\Facades\DB;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // --- 1. Temukan User yang Sudah Dibuat oleh UserSeeder ---
        $bendahara = User::where('email', 'bendahara.bumdes@example.com')->first();
        $manajer = User::where('email', 'manajer.unit.usaha@example.com')->first();

        if (!$bendahara || !$manajer) {
            $this->command->error('User bendahara atau manajer tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        // --- 2. Buat Unit Usaha ---
        $unitUsaha = UnitUsaha::create([
            'nama_unit' => 'Unit Usaha Seeder',
            'tanggal_mulai_operasi' => now(),
            'jenis_usaha' => 'Seeder Usaha',
            'status_operasi' => 'Aktif',
        ]);

        // Hubungkan manajer dengan unit usaha melalui tabel pivot
        $manajer->unitUsahas()->sync([$unitUsaha->unit_usaha_id]);

        // --- 3. Buat Akun Keuangan (minimal) ---
        $kasAkun = Akun::firstOrCreate(['kode_akun' => '1.1.01.01'], ['nama_akun' => 'Kas Tunai', 'tipe_akun' => 'Aset', 'is_header' => 0]);
        $persediaanAkun = Akun::firstOrCreate(['kode_akun' => '1.1.05.01'], ['nama_akun' => 'Persediaan Barang Dagangan', 'tipe_akun' => 'Aset', 'is_header' => 0]);
        $utangAkun = Akun::firstOrCreate(['kode_akun' => '2.1.01.01'], ['nama_akun' => 'Utang Usaha', 'tipe_akun' => 'Kewajiban', 'is_header' => 0]);
        $modalAkun = Akun::firstOrCreate(['kode_akun' => '3.1.01.01'], ['nama_akun' => 'Penyertaan Modal Desa', 'tipe_akun' => 'Ekuitas', 'is_header' => 0]);
        $pendapatanAkun = Akun::firstOrCreate(['kode_akun' => '4.1.01.01'], ['nama_akun' => 'Pendapatan Penjualan', 'tipe_akun' => 'Pendapatan', 'is_header' => 0]);
        $hppAkun = Akun::firstOrCreate(['kode_akun' => '5.1.01.01'], ['nama_akun' => 'Harga Pokok Penjualan', 'tipe_akun' => 'HPP', 'is_header' => 0]);
        $bebanAkun = Akun::firstOrCreate(['kode_akun' => '6.1.01.01'], ['nama_akun' => 'Beban Gaji', 'tipe_akun' => 'Beban', 'is_header' => 0]);

        // --- 4. Buat Transaksi Sesuai Siklus Akuntansi ---
        // Periode: 1 Agustus 2025 s/d 31 Agustus 2025

        // Transaksi 1: Modal Awal
        $jurnal1 = JurnalUmum::create([
            'user_id' => $bendahara->user_id,
            'unit_usaha_id' => $unitUsaha->unit_usaha_id,
            'tanggal_transaksi' => '2025-08-01',
            'deskripsi' => 'Setoran modal awal dari desa',
            'total_debit' => 10000000,
            'total_kredit' => 10000000,
            'status' => 'disetujui'
        ]);
        DetailJurnal::create(['jurnal_id' => $jurnal1->jurnal_id, 'akun_id' => $kasAkun->akun_id, 'debit' => 10000000, 'kredit' => 0]);
        DetailJurnal::create(['jurnal_id' => $jurnal1->jurnal_id, 'akun_id' => $modalAkun->akun_id, 'debit' => 0, 'kredit' => 10000000]);

        // Transaksi 2: Pembelian Barang Dagangan (Kredit)
        $jurnal2 = JurnalUmum::create([
            'user_id' => $manajer->user_id,
            'unit_usaha_id' => $unitUsaha->unit_usaha_id,
            'tanggal_transaksi' => '2025-08-05',
            'deskripsi' => 'Pembelian barang dagangan secara kredit',
            'total_debit' => 5000000,
            'total_kredit' => 5000000,
            'status' => 'disetujui'
        ]);
        DetailJurnal::create(['jurnal_id' => $jurnal2->jurnal_id, 'akun_id' => $persediaanAkun->akun_id, 'debit' => 5000000, 'kredit' => 0]);
        DetailJurnal::create(['jurnal_id' => $jurnal2->jurnal_id, 'akun_id' => $utangAkun->akun_id, 'debit' => 0, 'kredit' => 5000000]);

        // Transaksi 3: Penjualan Barang (Tunai)
        $jurnal3 = JurnalUmum::create([
            'user_id' => $manajer->user_id,
            'unit_usaha_id' => $unitUsaha->unit_usaha_id,
            'tanggal_transaksi' => '2025-08-10',
            'deskripsi' => 'Penjualan barang dagangan tunai',
            'total_debit' => 2000000,
            'total_kredit' => 2000000,
            'status' => 'disetujui'
        ]);
        DetailJurnal::create(['jurnal_id' => $jurnal3->jurnal_id, 'akun_id' => $kasAkun->akun_id, 'debit' => 2000000, 'kredit' => 0]);
        DetailJurnal::create(['jurnal_id' => $jurnal3->jurnal_id, 'akun_id' => $pendapatanAkun->akun_id, 'debit' => 0, 'kredit' => 2000000]);

        // Jurnal untuk Harga Pokok Penjualan
        $jurnal4 = JurnalUmum::create([
            'user_id' => $manajer->user_id,
            'unit_usaha_id' => $unitUsaha->unit_usaha_id,
            'tanggal_transaksi' => '2025-08-10',
            'deskripsi' => 'HPP untuk penjualan barang',
            'total_debit' => 1200000,
            'total_kredit' => 1200000,
            'status' => 'disetujui'
        ]);
        DetailJurnal::create(['jurnal_id' => $jurnal4->jurnal_id, 'akun_id' => $hppAkun->akun_id, 'debit' => 1200000, 'kredit' => 0]);
        DetailJurnal::create(['jurnal_id' => $jurnal4->jurnal_id, 'akun_id' => $persediaanAkun->akun_id, 'debit' => 0, 'kredit' => 1200000]);

        // Transaksi 4: Pembayaran Gaji Karyawan
        $jurnal5 = JurnalUmum::create([
            'user_id' => $manajer->user_id,
            'unit_usaha_id' => $unitUsaha->unit_usaha_id,
            'tanggal_transaksi' => '2025-08-25',
            'deskripsi' => 'Pembayaran gaji karyawan',
            'total_debit' => 500000,
            'total_kredit' => 500000,
            'status' => 'disetujui'
        ]);
        DetailJurnal::create(['jurnal_id' => $jurnal5->jurnal_id, 'akun_id' => $bebanAkun->akun_id, 'debit' => 500000, 'kredit' => 0]);
        DetailJurnal::create(['jurnal_id' => $jurnal5->jurnal_id, 'akun_id' => $kasAkun->akun_id, 'debit' => 0, 'kredit' => 500000]);
    }
}
