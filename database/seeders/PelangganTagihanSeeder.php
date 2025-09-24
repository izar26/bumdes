<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Illuminate\Support\Facades\File;

class PelangganTagihanSeeder extends Seeder
{
    public function run(): void
    {
        $files = File::files(database_path('seeders/csv'));

        foreach ($files as $file) {
           $csv = Reader::createFromPath($file->getPathname(), 'r');

// baca header mentah
$headers = $csv->fetchOne();

// hilangkan duplikat dengan kasih suffix
$uniqueHeaders = [];
foreach ($headers as $index => $header) {
    $count = array_count_values($headers)[$header];
    if ($count > 1) {
        $headers[$index] = $header . '_' . $index;
    }
    $uniqueHeaders[] = $headers[$index];
}

// set ulang header offset
$csv->setHeaderOffset(0);
$csv->setHeader($uniqueHeaders);
            $csv->setHeaderOffset(0); // pakai header dari file

            foreach ($csv as $record) {
                // Insert pelanggan jika belum ada
                $pelangganId = DB::table('pelanggan')->insertGetId([
                    'nama' => $record['nama'] ?? 'Tanpa Nama',
                    'alamat' => $record['alamat'] ?? '-',
                    'status_pelanggan' => $record['status_pelanggan'] ?? 'Aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Ambil petugas berdasarkan nama file
                $namaFile = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $petugasNama = explode('_', $namaFile)[1] ?? null;
                $petugasId = DB::table('petugas')->where('nama_petugas', 'LIKE', "%$petugasNama%")->value('id');

                // Insert tagihan
                DB::table('tagihan')->insert([
                    'pelanggan_id' => $pelangganId,
                    'petugas_id' => $petugasId,
                    'periode_tagihan' => $record['periode_tagihan'] ?? now(),
                    'tanggal_cetak' => $record['tanggal_cetak'] ?? null,
                    'meter_awal' => $record['meter_awal'] ?? 0,
                    'meter_akhir' => $record['meter_akhir'] ?? 0,
                    'total_pemakaian_m3' => $record['total_pemakaian_m3'] ?? 0,
                    'subtotal_pemakaian' => $record['subtotal_pemakaian'] ?? 0,
                    'biaya_lainnya' => $record['biaya_lainnya'] ?? 0,
                    'denda' => $record['denda'] ?? 0,
                    'tunggakan' => $record['tunggakan'] ?? 0,
                    'total_harus_dibayar' => $record['total_harus_dibayar'] ?? 0,
                    'status_pembayaran' => $record['status_pembayaran'] ?? 'Belum Lunas',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
