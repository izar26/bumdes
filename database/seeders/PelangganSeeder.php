<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pelanggan;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menonaktifkan pemeriksaan foreign key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus semua data pelanggan yang sudah ada di tabel
        Pelanggan::truncate();

        // Mengaktifkan kembali pemeriksaan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Array untuk menyimpan pelanggan unik
        $customers = [];

        // Daftar file CSV yang akan diproses
        $files = [
            'KOLEKTORDIDAN.csv',
            'KOLEKTORDIDAN1.csv',
            'KOLEKTORDADANG.csv',
            'KOLEKTOROBI.csv'
        ];

        foreach ($files as $file) {
            $path = database_path("seeders/csv/{$file}");

            if (!file_exists($path)) {
                $this->command->error("File tidak ditemukan: {$path}");
                continue;
            }

            $csvData = array_map('str_getcsv', file($path));

            // Lewati baris header
            array_shift($csvData);

            foreach ($csvData as $row) {
                // Lewati baris kosong atau baris summary
                if (empty($row[2]) || empty($row[3]) || $row[0] === ' ') {
                    continue;
                }

                $nama = trim($row[2]);
                $alamat = trim($row[3]);

                // Buat kunci unik untuk menghindari duplikasi
                $uniqueKey = "{$nama}|{$alamat}";

                if (!isset($customers[$uniqueKey])) {
                    $customers[$uniqueKey] = [
                        'nama' => $nama,
                        'alamat' => $alamat,
                        'status_pelanggan' => 'Aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Masukkan pelanggan ke database dalam batch
        if (!empty($customers)) {
            $chunks = array_chunk($customers, 500);
            foreach ($chunks as $chunk) {
                DB::table('pelanggan')->insert($chunk);
            }

            $this->command->info(count($customers) . ' pelanggan berhasil ditambahkan.');
        } else {
            $this->command->warn('Tidak ada pelanggan yang ditemukan untuk ditambahkan.');
        }
    }
}
