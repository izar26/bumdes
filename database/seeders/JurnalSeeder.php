<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use App\Models\Akun;
use App\Models\User;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\DB;

class JurnalSeeder extends Seeder
{
    public function run(): void
    {
        $createJurnal = function ($tanggal, $deskripsi, $userId, $unitUsahaId, $details) {
            $totalDebit = collect($details)->sum('debit');
            $totalKredit = collect($details)->sum('kredit');

            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                throw new \Exception("Jurnal tidak seimbang untuk: " . $deskripsi);
            }

            $jurnal = JurnalUmum::create([
                'user_id' => $userId,
                'unit_usaha_id' => $unitUsahaId,
                'tanggal_transaksi' => $tanggal,
                'deskripsi' => $deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
            ]);

            foreach ($details as $detail) {
                // Diganti menjadi firstOrFail untuk memastikan error jika akun tidak ada
                $akun = Akun::where('kode_akun', $detail['kode_akun'])->firstOrFail();
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $akun->akun_id,
                    'debit' => $detail['debit'],
                    'kredit' => $detail['kredit'],
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }
        };

        $bendahara = User::where('username', 'bendahara_bumdes')->first();
        $adminUnit = User::where('username', 'admin_unit_usaha')->first();
        $unitToko = UnitUsaha::where('nama_unit', 'Toko BUMDes')->first();
        $unitWisata = UnitUsaha::where('nama_unit', 'Wisata Desa')->first();

        // --- CONTOH TRANSAKSI (KODE AKUN SUDAH DISESUAIKAN) ---

        $createJurnal('2025-07-01', 'Setoran modal awal dari Desa', $bendahara->user_id, null, [
            ['kode_akun' => '1.1.01.02', 'debit' => 50000000, 'kredit' => 0], // Kas di Bank BSI
            ['kode_akun' => '3.1.01.01', 'debit' => 0, 'kredit' => 50000000], // Penyertaan Modal Desa
        ]);

        $createJurnal('2025-07-05', 'Pembelian stok awal toko secara kredit', $adminUnit->user_id, $unitToko->unit_usaha_id, [
            ['kode_akun' => '1.1.05.01', 'debit' => 5000000, 'kredit' => 0], // Persediaan
            ['kode_akun' => '2.1.01.01', 'debit' => 0, 'kredit' => 5000000], // Utang Usaha
        ]);

        $createJurnal('2025-07-10', 'Penjualan tunai pertama di toko', $adminUnit->user_id, $unitToko->unit_usaha_id, [
            ['kode_akun' => '1.1.01.01', 'debit' => 750000, 'kredit' => 0], // Kas Tunai
            ['kode_akun' => '4.2.01.91', 'debit' => 0, 'kredit' => 750000], // Pendapatan Penjualan Barang Dagangan
        ]);

        $createJurnal('2025-07-15', 'Pembayaran biaya promosi Unit Wisata', $bendahara->user_id, $unitWisata->unit_usaha_id, [
            ['kode_akun' => '6.3.02.01', 'debit' => 1200000, 'kredit' => 0], // Beban Iklan
            ['kode_akun' => '1.1.01.02', 'debit' => 0, 'kredit' => 1200000], // Kas di Bank BSI
        ]);
    }
}