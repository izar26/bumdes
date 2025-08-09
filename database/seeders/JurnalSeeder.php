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
        // Pastikan unit usaha yang diperlukan ada — bila belum ada, buat dengan kolom wajib
        $unitToko = UnitUsaha::firstOrCreate(
            ['nama_unit' => 'Toko BUMDes'],
            [
                'jenis_usaha' => 'Perdagangan',
                'tanggal_mulai_operasi' => '2022-01-15',
                'status_operasi' => 'Aktif',
                'user_id' => null,
            ]
        );

        $unitWisata = UnitUsaha::firstOrCreate(
            ['nama_unit' => 'Wisata Desa'],
            [
                'jenis_usaha' => 'Pariwisata',
                'tanggal_mulai_operasi' => '2024-05-20',
                'status_operasi' => 'Aktif',
                'user_id' => null,
            ]
        );

        // Ambil user yang akan dipakai sebagai pembuat jurnal
        $bendahara = User::where('username', 'bendahara_bumdes')->first();
        $adminUnit = User::where('username', 'admin_unit_usaha')->first();

        if (!$bendahara || !$adminUnit) {
            $this->command->error('User "bendahara_bumdes" or "admin_unit_usaha" not found. Please run UserSeeder first.');
            return;
        }

        // Helper untuk membuat jurnal + detail (memastikan akun ada)
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
                // pastikan status default ada — misal 'menunggu'
                'status' => 'menunggu',
            ]);

            foreach ($details as $detail) {
                $akun = Akun::where('kode_akun', $detail['kode_akun'])->first();
                if (!$akun) {
                    // bila akun tidak ada, hentikan agar kita tahu apa yang perlu ditambahkan
                    throw new \Exception("Akun dengan kode {$detail['kode_akun']} tidak ditemukan.");
                }

                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $akun->akun_id,
                    'debit' => $detail['debit'],
                    'kredit' => $detail['kredit'],
                    'keterangan' => $detail['keterangan'] ?? null,
                ]);
            }
        };

        // --- Data contoh ---
        $createJurnal('2025-07-01', 'Setoran modal awal dari Desa', $bendahara->user_id, null, [
            ['kode_akun' => '1.1.01.02', 'debit' => 50000000, 'kredit' => 0],
            ['kode_akun' => '3.1.01.01', 'debit' => 0, 'kredit' => 50000000],
        ]);

        $createJurnal('2025-07-05', 'Pembelian stok awal toko secara kredit', $adminUnit->user_id, $unitToko->unit_usaha_id, [
            ['kode_akun' => '1.1.05.01', 'debit' => 5000000, 'kredit' => 0],
            ['kode_akun' => '2.1.01.01', 'debit' => 0, 'kredit' => 5000000],
        ]);

        $createJurnal('2025-07-10', 'Penjualan tunai pertama di toko', $adminUnit->user_id, $unitToko->unit_usaha_id, [
            ['kode_akun' => '1.1.01.01', 'debit' => 750000, 'kredit' => 0],
            ['kode_akun' => '4.2.01.91', 'debit' => 0, 'kredit' => 750000],
        ]);

        $createJurnal('2025-07-15', 'Pembayaran biaya promosi Unit Wisata', $bendahara->user_id, $unitWisata->unit_usaha_id, [
            ['kode_akun' => '6.3.02.01', 'debit' => 1200000, 'kredit' => 0],
            ['kode_akun' => '1.1.01.02', 'debit' => 0, 'kredit' => 1200000],
        ]);
    }
}
