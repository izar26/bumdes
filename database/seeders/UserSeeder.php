<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Menonaktifkan pemeriksaan foreign key sementara
        Schema::disableForeignKeyConstraints();

        // 1. Hapus semua data pengguna yang ada untuk menghindari duplikasi saat seeding
        User::truncate();

        // 2. Buat Roles jika belum ada (gunakan Role::firstOrCreate)
        $adminBumdesRole = Role::firstOrCreate(['name' => 'admin_bumdes']);
        $manajerUnitUsahaRole = Role::firstOrCreate(['name' => 'manajer_unit_usaha']);
        $bendaharaBumdesRole = Role::firstOrCreate(['name' => 'bendahara_bumdes']);
        $kepdesRole = Role::firstOrCreate(['name' => 'kepala_desa']);
        
        // --- Perubahan #1: Tambahkan Peran 'admin_unit_usaha' ---
        $adminUnitUsahaRole = Role::firstOrCreate(['name' => 'admin_unit_usaha']);
        // --- Akhir Perubahan #1 ---

        // 3. Buat Permissions jika belum ada (gunakan Permission::firstOrCreate)
        $viewUsersPermission = Permission::firstOrCreate(['name' => 'view users']);
        $createUsersPermission = Permission::firstOrCreate(['name' => 'create users']);
        $editUsersPermission = Permission::firstOrCreate(['name' => 'edit users']);
        $deleteUsersPermission = Permission::firstOrCreate(['name' => 'delete users']);
        $verifyJurnalPermission = Permission::firstOrCreate(['name' => 'verify jurnal']);
        $approveLaporanPermission = Permission::firstOrCreate(['name' => 'approve laporan']);
        $inputJurnalPermission = Permission::firstOrCreate(['name' => 'input jurnal']);
        $viewLaporanKeuanganPermission = Permission::firstOrCreate(['name' => 'view laporan keuangan']);

        // --- Perubahan #2: Tambahkan Permissions baru dan kaitkan dengan peran ---
        $editUnitUsahaProfilePermission = Permission::firstOrCreate(['name' => 'edit unit_usaha profile']);
        // --- Akhir Perubahan #2 ---

        // 4. Assign Permissions ke Roles
        $adminBumdesRole->syncPermissions([
            $viewUsersPermission,
            $createUsersPermission,
            $editUsersPermission,
            $deleteUsersPermission
        ]);
        $manajerUnitUsahaRole->syncPermissions([
            $viewUsersPermission,
            $verifyJurnalPermission
        ]);
        $bendaharaBumdesRole->syncPermissions([
            $viewUsersPermission,
            $inputJurnalPermission,
            $verifyJurnalPermission,
            $viewLaporanKeuanganPermission
        ]);
        $kepdesRole->syncPermissions([
            $approveLaporanPermission
        ]);

        // --- Perubahan #3: Assign permissions ke Peran 'admin_unit_usaha' ---
        $adminUnitUsahaRole->syncPermissions([
            $inputJurnalPermission,
            $editUnitUsahaProfilePermission
        ]);
        // --- Akhir Perubahan #3 ---


        // 5. Buat pengguna (users) dan tetapkan peran (role)
        $adminBumdes = User::create([
            'name' => 'Admin BUMDes',
            'email' => 'admin.bumdes@example.com',
            'username' => 'admin_bumdes',
            'password' => Hash::make('password'),
            'role' => 'admin_bumdes',
            'is_active' => true,
        ]);
        $adminBumdes->assignRole($adminBumdesRole);

        $manajerUnit = User::create([
            'name' => 'Manajer Unit Wisata',
            'email' => 'manajer.wisata@example.com',
            'username' => 'manajer_wisata',
            'password' => Hash::make('password'),
            'role' => 'manajer_unit_usaha',
            'is_active' => true,
        ]);
        $manajerUnit->assignRole($manajerUnitUsahaRole);

        $bendahara = User::create([
            'name' => 'Bendahara BUMDes',
            'email' => 'bendahara.bumdes@example.com',
            'username' => 'bendahara_bumdes',
            'password' => Hash::make('password'),
            'role' => 'bendahara_bumdes',
            'is_active' => true,
        ]);
        $bendahara->assignRole($bendaharaBumdesRole);

        $kepdes = User::create([
            'name' => 'Kepala Desa',
            'email' => 'kepala.desa@example.com',
            'username' => 'kepdes',
            'password' => Hash::make('password'),
            'role' => 'kepala_desa',
            'is_active' => true,
        ]);
        $kepdes->assignRole($kepdesRole);

        $adminUnitUsaha = User::create([
            'name' => 'Admin Unit Usaha',
            'email' => 'admin.unit.usaha@example.com',
            'username' => 'admin_unit_usaha',
            'password' => Hash::make('password'),
            'role' => 'admin_unit_usaha',
            'is_active' => true,
        ]);
        $adminUnitUsaha->assignRole($adminUnitUsahaRole);
        // --- Akhir Perubahan #4 ---

        // Mengaktifkan kembali pemeriksaan foreign key
        Schema::enableForeignKeyConstraints();
    }
}
