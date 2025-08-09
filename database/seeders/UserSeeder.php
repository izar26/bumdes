<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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

        // 1. Hapus semua data pengguna, peran, dan perizinan
        User::truncate();
        Role::truncate();
        Permission::truncate();

        // 2. Buat Roles (Peran)
        // Perbaikan: Tambahkan peran direktur_bumdes dan admin_bumdes
        $direkturBumdesRole = Role::firstOrCreate(['name' => 'direktur_bumdes']);
        $adminBumdesRole = Role::firstOrCreate(['name' => 'admin_bumdes']);
        $sekretarisBumdesRole = Role::firstOrCreate(['name' => 'sekretaris_bumdes']);
        $manajerUnitUsahaRole = Role::firstOrCreate(['name' => 'manajer_unit_usaha']);
        $bendaharaBumdesRole = Role::firstOrCreate(['name' => 'bendahara_bumdes']);
        $kepdesRole = Role::firstOrCreate(['name' => 'kepala_desa']);
        $adminUnitUsahaRole = Role::firstOrCreate(['name' => 'admin_unit_usaha']);
        $anggotaRole = Role::firstOrCreate(['name' => 'anggota']);

        // 3. Buat Permissions (Perizinan)
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users']);
        $manageBumdesProfilePermission = Permission::firstOrCreate(['name' => 'manage bumdes profile']);
        $manageUnitUsahaPermission = Permission::firstOrCreate(['name' => 'manage unit usaha']);
        $manageBeritaPotensiPermission = Permission::firstOrCreate(['name' => 'manage berita & potensi']);
        $manageKeuanganPermission = Permission::firstOrCreate(['name' => 'manage keuangan']);
        $manageAsetPermission = Permission::firstOrCreate(['name' => 'manage aset']);
        $viewLaporanKeuanganPermission = Permission::firstOrCreate(['name' => 'view laporan keuangan']);
        $manageLaporanKeuanganPermission = Permission::firstOrCreate(['name' => 'manage laporan keuangan']);

        // 4. Assign Permissions ke Roles
        $adminBumdesRole->syncPermissions([
            $manageUsersPermission,
            $manageBumdesProfilePermission,
            $manageUnitUsahaPermission,
            $manageBeritaPotensiPermission,
            $manageLaporanKeuanganPermission,
        ]);

        // Peran direktur hanya bisa melihat laporan
        $direkturBumdesRole->syncPermissions([
            $viewLaporanKeuanganPermission
        ]);

        $sekretarisBumdesRole->syncPermissions([
            $viewLaporanKeuanganPermission
        ]);

        $manajerUnitUsahaRole->syncPermissions([
            $manageKeuanganPermission,
            $viewLaporanKeuanganPermission,
            $manageUnitUsahaPermission
        ]);

        $bendaharaBumdesRole->syncPermissions([
            $manageKeuanganPermission,
            $manageAsetPermission,
            $viewLaporanKeuanganPermission
        ]);

        $kepdesRole->syncPermissions([
            $viewLaporanKeuanganPermission
        ]);

        $adminUnitUsahaRole->syncPermissions([
            $manageKeuanganPermission,
            $viewLaporanKeuanganPermission,
            $manageUnitUsahaPermission
        ]);

        $anggotaRole->syncPermissions([]); // Anggota tidak memiliki perizinan khusus

        // 5. Buat pengguna (users) dan tetapkan peran (role)
        $direkturBumdes = User::create([
            'name' => 'Direktur BUMDes',
            'email' => 'direktur.bumdes@example.com',
            'username' => 'direktur_bumdes',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $direkturBumdes->assignRole($direkturBumdesRole);

        $adminBumdes = User::create([
            'name' => 'Admin BUMDes',
            'email' => 'admin.bumdes@example.com',
            'username' => 'admin_bumdes',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $adminBumdes->assignRole($adminBumdesRole);

        $sekretarisBumdes = User::create([
            'name' => 'Sekretaris BUMDes',
            'email' => 'sekretaris.bumdes@example.com',
            'username' => 'sekretaris_bumdes',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $sekretarisBumdes->assignRole($sekretarisBumdesRole);

        $manajerUnit = User::create([
            'name' => 'Manajer Unit Usaha',
            'email' => 'manajer.unit.usaha@example.com',
            'username' => 'manajer_unit_usaha',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manajerUnit->assignRole($manajerUnitUsahaRole);

        $bendahara = User::create([
            'name' => 'Bendahara BUMDes',
            'email' => 'bendahara.bumdes@example.com',
            'username' => 'bendahara_bumdes',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $bendahara->assignRole($bendaharaBumdesRole);

        $kepdes = User::create([
            'name' => 'Kepala Desa',
            'email' => 'kepala.desa@example.com',
            'username' => 'kepala_desa',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $kepdes->assignRole($kepdesRole);

        $adminUnitUsaha = User::create([
            'name' => 'Admin Unit Usaha',
            'email' => 'admin.unit.usaha@example.com',
            'username' => 'admin_unit_usaha',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $adminUnitUsaha->assignRole($adminUnitUsahaRole);

        // Mengaktifkan kembali pemeriksaan foreign key
        Schema::enableForeignKeyConstraints();
    }
}
