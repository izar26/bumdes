<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
            UnitUsahaSeeder::class,
            AkunSeeder::class,
            JurnalSeeder::class,
            ProfilSeeder::class,
            HomepageSettingSeeder::class,
            BungdesSeeder::class,
        ]);
    }
}
