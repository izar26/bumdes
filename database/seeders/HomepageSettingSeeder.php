<?php

// database/seeders/HomepageSettingSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomepageSetting;

class HomepageSettingSeeder extends Seeder
{
    public function run(): void
    {
        HomepageSetting::create([
            'hero_headline' => 'Selamat Datang di Desa Sukamaju',
            'hero_tagline' => 'Mewujudkan Desa yang Mandiri, Sejahtera, dan Berbasis Digital.',
            // Biarkan gambar null pada awalnya
        ]);
    }
}