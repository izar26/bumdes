<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Akun;

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Kosongkan tabel dengan aman
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Akun::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Tentukan path ke file CSV
        $csvFile = fopen(database_path('seeders/data/akun.csv'), 'r');

        // Baca baris pertama (header) untuk mendapatkan nama kolom
        $headers = fgetcsv($csvFile);

        // 3. Loop melalui sisa baris data
        while (($row = fgetcsv($csvFile)) !== false) {
            // Gabungkan header dengan baris data menjadi array asosiatif
            $data = array_combine($headers, $row);

            // 4. Proses data sebelum dimasukkan
            // Konversi 'true'/'false' string dari CSV menjadi boolean
            $data['is_header'] = strtolower($data['is_header']) === 'true';

            // Konversi parent_id yang kosong menjadi null
            if (empty($data['parent_id'])) {
                $data['parent_id'] = null;
            }

            // 5. Buat record baru di database
            Akun::create($data);
        }

        // 6. Tutup file
        fclose($csvFile);
    }
}