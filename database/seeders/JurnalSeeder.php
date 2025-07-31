<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use App\Models\Akun;
use Illuminate\Support\Facades\DB;

class JurnalSeeder extends Seeder
{
    public function run(): void
    {
        // Helper function untuk membuat jurnal
        $createJurnal = function ($tanggal, $deskripsi, $details) {
            $totalDebit = collect($details)->sum('debit');
            $totalKredit = collect($details)->sum('kredit');

            if ($totalDebit !== $totalKredit) {
                throw new \Exception("Jurnal tidak seimbang untuk: " . $deskripsi);
            }

            $jurnal = JurnalUmum::create([
                // 'bungdes_id' => 1,
                'user_id' => 1,
                'tanggal_transaksi' => $tanggal,
                'deskripsi' => $deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
            ]);

            foreach ($details as $detail) {
                $akun = Akun::where('kode_akun', $detail['kode_akun'])->first();
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $akun->akun_id,
                    'debit' => $detail['debit'],
                    'kredit' => $detail['kredit'],
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }
        };

        // --- CONTOH TRANSAKSI UNTUK JULI 2025 ---

        // 1. Setoran Modal Awal
        $createJurnal('2025-07-01', 'Setoran modal awal dari Desa', [
            ['kode_akun' => '1.1.01.01', 'debit' => 10000000, 'kredit' => 0], // Kas di Tangan
            ['kode_akun' => '3.1.01.01', 'debit' => 0, 'kredit' => 10000000], // Penyertaan Modal Desa
        ]);

        // 2. Pembelian Barang secara Kredit
        $createJurnal('2025-07-02', 'Pembelian barang dagangan dari Pemasok A', [
            ['kode_akun' => '1.1.05.01', 'debit' => 2000000, 'kredit' => 0], // Persediaan Barang Dagangan
            ['kode_akun' => '2.1.01.01', 'debit' => 0, 'kredit' => 2000000], // Utang Usaha
        ]);

        // 3. Penjualan Tunai
        $createJurnal('2025-07-05', 'Penjualan tunai di toko', [
            ['kode_akun' => '1.1.01.01', 'debit' => 1500000, 'kredit' => 0], // Kas di Tangan
            ['kode_akun' => '4.2.01.91', 'debit' => 0, 'kredit' => 1500000], // Pendapatan Penjualan Barang Dagangan
        ]);

        // 4. Pembayaran Beban Listrik
        $createJurnal('2025-07-10', 'Pembayaran beban listrik bulan Juni', [
            ['kode_akun' => '6.1.04.01', 'debit' => 250000, 'kredit' => 0], // Beban Listrik
            ['kode_akun' => '1.1.01.01', 'debit' => 0, 'kredit' => 250000], // Kas di Tangan
        ]);

        // 5. Penjualan Kredit
        $createJurnal('2025-07-15', 'Penjualan kredit kepada Pelanggan B', [
            ['kode_akun' => '1.1.03.01', 'debit' => 1000000, 'kredit' => 0], // Piutang Usaha
            ['kode_akun' => '4.2.01.91', 'debit' => 0, 'kredit' => 1000000], // Pendapatan Penjualan Barang Dagangan
        ]);

        // 6. Pembayaran Gaji 2 Karyawan
        $createJurnal('2025-07-25', 'Pembayaran gaji Juli untuk Budi dan Ani', [
            ['kode_akun' => '6.1.01.01', 'debit' => 700000, 'kredit' => 0, 'keterangan' => 'Gaji Budi'], // Beban Gaji
            ['kode_akun' => '6.1.01.01', 'debit' => 800000, 'kredit' => 0, 'keterangan' => 'Gaji Ani'], // Beban Gaji
            ['kode_akun' => '1.1.01.01', 'debit' => 0, 'kredit' => 1500000], // Kas di Tangan
        ]);
    }
}