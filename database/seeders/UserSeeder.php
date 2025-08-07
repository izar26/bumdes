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
        Role::truncate();
        Permission::truncate();

        // 2. Buat Roles jika belum ada (gunakan Role::firstOrCreate)
        $direkturBumdesRole = Role::firstOrCreate(['name' => 'admin_bumdes']);
        $sekretarisBumdesRole = Role::firstOrCreate(['name' => 'sekretaris_bumdes']);
        $manajerUnitUsahaRole = Role::firstOrCreate(['name' => 'manajer_unit_usaha']);
        $bendaharaBumdesRole = Role::firstOrCreate(['name' => 'bendahara_bumdes']);
        $kepdesRole = Role::firstOrCreate(['name' => 'kepala_desa']);
        $adminUnitUsahaRole = Role::firstOrCreate(['name' => 'admin_unit_usaha']);

        // 3. Buat Permissions jika belum ada
        $viewUsersPermission = Permission::firstOrCreate(['name' => 'view users']);
        $createUsersPermission = Permission::firstOrCreate(['name' => 'create users']);
        $editUsersPermission = Permission::firstOrCreate(['name' => 'edit users']);
        $deleteUsersPermission = Permission::firstOrCreate(['name' => 'delete users']);
        $approveTransaksiBendaharaPermission = Permission::firstOrCreate(['name' => 'approve transaksi bendahara']);
        $approveLaporanPermission = Permission::firstOrCreate(['name' => 'approve laporan']);
        $inputJurnalPermission = Permission::firstOrCreate(['name' => 'input jurnal']);
        $viewLaporanKeuanganPermission = Permission::firstOrCreate(['name' => 'view laporan keuangan']);
        $editLaporanKeseluruhanPermission = Permission::firstOrCreate(['name' => 'edit laporan keseluruhan']);

        // 4. Assign Permissions ke Roles
        $direkturBumdesRole->syncPermissions([
            $viewUsersPermission,
            $createUsersPermission,
            $editUsersPermission,
            $deleteUsersPermission,
            $approveTransaksiBendaharaPermission,
            $viewLaporanKeuanganPermission
        ]);
        $sekretarisBumdesRole->syncPermissions([
            $viewLaporanKeuanganPermission,
            $editLaporanKeseluruhanPermission // Hanya jika sudah diverifikasi dan ditolak direktur
        ]);
        $manajerUnitUsahaRole->syncPermissions([
            $inputJurnalPermission,
            $approveLaporanPermission
        ]);
        $bendaharaBumdesRole->syncPermissions([
            $inputJurnalPermission,
            $viewLaporanKeuanganPermission
        ]);
        $kepdesRole->syncPermissions([
            $approveLaporanPermission
        ]);
        $adminUnitUsahaRole->syncPermissions([
            $inputJurnalPermission,
            $viewLaporanKeuanganPermission
        ]);


        // 5. Buat pengguna (users) dan tetapkan peran (role)
        $direkturBumdes = User::create([
            'name' => 'Direktur BUMDes',
            'email' => 'direktur.bumdes@example.com',
            'username' => 'admin_bumdes',
            'password' => Hash::make('password'),
            'role' => 'admin_bumdes',
            'is_active' => true,
        ]);
        $direkturBumdes->assignRole($direkturBumdesRole);

        $sekretarisBumdes = User::create([
            'name' => 'Sekretaris BUMDes',
            'email' => 'sekretaris.bumdes@example.com',
            'username' => 'sekretaris_bumdes',
            'password' => Hash::make('password'),
            'role' => 'sekretaris_bumdes',
            'is_active' => true,
        ]);
        $sekretarisBumdes->assignRole($sekretarisBumdesRole);

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

        // Mengaktifkan kembali pemeriksaan foreign key
        Schema::enableForeignKeyConstraints();
    }
}
