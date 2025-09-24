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
        // Clear existing data
        Pelanggan::query()->delete();

        // Array to store unique customers
        $customers = [];

        // Process each CSV file
        $files = [
            '5_KOLEKTOR DINDAN_1_SEPTEMBER_25.csv',
            '3_KOLEKTOR DINDAN 1_SEPTEMBER_25.csv',
            '1_KOLEKTOR OBI_SEPTEMBER_25.csv',
            '2_KOLEKTOR DADANG_SEPTEMBER_25.csv'
        ];

        foreach ($files as $file) {
            $path = database_path("seeders/csv/{$file}");

            if (!file_exists($path)) {
                $this->command->error("File not found: {$path}");
                continue;
            }

            $csvData = array_map('str_getcsv', file($path));

            // Skip header row
            array_shift($csvData);

            foreach ($csvData as $row) {
                // Skip empty rows or summary rows
                if (empty($row[3]) || empty($row[4]) || $row[0] === ' ') {
                    continue;
                }

                $nama = trim($row[3]);
                $alamat = trim($row[4]);

                // Create unique key to avoid duplicates
                $uniqueKey = $nama . '|' . $alamat;

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

        // Insert customers in batches
        if (!empty($customers)) {
            $chunks = array_chunk($customers, 500);
            foreach ($chunks as $chunk) {
                Pelanggan::insert($chunk);
            }

            $this->command->info(count($customers) . ' customers seeded successfully.');
        } else {
            $this->command->warn('No customers found to seed.');
        }
    }
}
