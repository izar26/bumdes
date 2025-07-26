<?php

// database/seeders/ProfilSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profil;

class ProfilSeeder extends Seeder
{
    public function run(): void
    {
        Profil::create([
            'nama_desa' => 'Desa Sukamaju',
            'deskripsi' => 'Desa Sukamaju merupakan sebuah desa asri yang terletak di kaki gunung, dikelilingi oleh hamparan sawah hijau yang subur. Dengan visi untuk menjadi desa digital yang mandiri, kami terus berinovasi dalam pelayanan publik dan pengembangan potensi lokal.',
            'jumlah_penduduk' => 3500,
            'jumlah_kk' => 1200,
            'luas_wilayah' => '500 Ha',
            'alamat' => 'Jalan Raya Sejahtera No. 1, Kecamatan Makmur, Kabupaten Sentosa, 54321.',
            'email' => 'kontak@sukamaju.desa.id',
            'telepon' => '(021) 123-456',
        ]);
    }
}