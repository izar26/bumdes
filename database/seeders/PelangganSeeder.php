<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            database_path('seeders/csv/dadang.csv'),
            database_path('seeders/csv/dindan1.csv'),
            database_path('seeders/csv/dindan2.csv'),
            database_path('seeders/csv/obi.csv'),
        ];

        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $csv = Reader::createFromPath($file, 'r');
            $csv->setHeaderOffset(null); // <== Abaikan header, treat semua baris sebagai data

            foreach ($csv as $index => $row) {
                // skip baris pertama (header manual)
                if ($index === 0) {
                    continue;
                }

                $nama   = $row[2] ?? null;
                $alamat = $row[3] ?? null;

                if ($nama && $alamat) {
                    DB::table('pelanggan')->insert([
                        'nama' => $nama,
                        'alamat' => $alamat,
                        'status_pelanggan' => 'Aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
