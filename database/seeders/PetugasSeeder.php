<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Petugas;

class PetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        Petugas::query()->delete();

        // Collectors from CSV filenames
        $collectors = [
            ['nama_petugas' => 'KOLEKTOR DINDAN', 'status' => 'Aktif'],
            ['nama_petugas' => 'KOLEKTOR OBI', 'status' => 'Aktif'],
            ['nama_petugas' => 'KOLEKTOR DADANG', 'status' => 'Aktif'],
        ];

        foreach ($collectors as $collector) {
            Petugas::create($collector);
        }

        $this->command->info('Petugas seeded successfully.');
    }
}
