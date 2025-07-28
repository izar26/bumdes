<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bungdes; // Import model Bungdes Anda
use Carbon\Carbon; // Untuk memudahkan pengelolaan tanggal

class BungdesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh data BUMDes
        Bungdes::firstOrCreate(
            ['email' => 'bumdes_maju@example.com'], // Kriteria untuk firstOrCreate (misalnya, email unik)
            [
                'nama_bumdes' => 'BUMDes Maju Bersama',
                'alamat' => 'Desa Makmur Jaya, Kecamatan Sejahtera, Kabupaten Gemah Ripah',
                'tanggal_berdiri' => Carbon::parse('2018-03-15'), // Menggunakan Carbon untuk tanggal
                'deskripsi' => 'BUMDes yang berfokus pada pengembangan ekonomi lokal melalui unit usaha pertanian dan pariwisata.',
                'telepon' => '081234567890',
                'struktur_organisasi' => 'path/to/struktur_organisasi_maju.jpg', // Contoh path file gambar
                'logo' => 'path/to/logo_maju.png', // Contoh path file gambar
                'aset_usaha' => 'Pertanian (sawah, kebun), Homestay, Unit Simpan Pinjam',
                'email' => 'bumdes_maju@example.com',
            ]
        );

        Bungdes::firstOrCreate(
            ['email' => 'bumdes_sejahtera@example.com'],
            [
                'nama_bumdes' => 'BUMDes Sejahtera Abadi',
                'alamat' => 'Jalan Damai No. 10, Desa Sentosa, Kota Bahagia',
                'tanggal_berdiri' => Carbon::parse('2020-07-20'),
                'deskripsi' => 'Fokus pada kerajinan tangan lokal dan pengelolaan sampah terpadu.',
                'telepon' => '087654321098',
                'struktur_organisasi' => 'path/to/struktur_organisasi_sejahtera.pdf',
                'logo' => 'path/to/logo_sejahtera.jpeg',
                'aset_usaha' => 'Pusat Kerajinan, Bank Sampah, Koperasi',
                'email' => 'bumdes_sejahtera@example.com',
            ]
        );

    }
}
