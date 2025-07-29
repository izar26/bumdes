<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bungdes; // Import model Bungdes Anda
use Carbon\Carbon; // Untuk memudahkan pengelolaan tanggal

class BungdesSeeder extends Seeder
{
    public function run(): void
    {
        Bungdes::firstOrCreate(
            ['email' => 'bumdes_maju@example.com'],
            [
                'nama_bumdes' => 'BUMDes Maju Bersama',
                'alamat' => 'Desa Makmur Jaya, Kecamatan Sejahtera, Kabupaten Gemah Ripah',
                'tanggal_berdiri' => Carbon::parse('2018-03-15'),
                'deskripsi' => 'BUMDes yang berfokus pada pengembangan ekonomi lokal melalui unit usaha pertanian dan pariwisata.',
                'telepon' => '081234567890',
                'struktur_organisasi' => 'path/to/struktur_organisasi_maju.jpg', // Contoh path file gambar
                'logo' => 'path/to/logo_maju.png', // Contoh path file gambar
                'aset_usaha' => 'Pertanian (sawah, kebun), Homestay, Unit Simpan Pinjam',
                'email' => 'bumdes_maju@example.com',
            ]
        );


    }
}
