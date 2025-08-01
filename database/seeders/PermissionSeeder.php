<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions untuk User Management
        Permission::firstOrCreate(['name' => 'manage users']); // Permission umum untuk semua aksi manajemen user
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'edit users']);
        Permission::firstOrCreate(['name' => 'delete users']);

        // Permissions untuk Verifikasi Jurnal
        Permission::firstOrCreate(['name' => 'verify jurnal']);

        // Permissions untuk Laporan Keuangan
        Permission::firstOrCreate(['name' => 'approve laporan']);

        // ... tambahkan permissions lain untuk modul lain seperti jurnal, buku besar, dll.

        // Roles yang sudah ada di sistem Anda
        $adminBumdesRole = Role::firstOrCreate(['name' => 'admin_bumdes']);
        $kepdesRole = Role::firstOrCreate(['name' => 'kepala_desa']);
        $manajerUnitUsahaRole = Role::firstOrCreate(['name' => 'manajer_unit_usaha']);

        // Assign permissions ke roles
        $adminBumdesRole->syncPermissions(['manage users', 'create users', 'edit users', 'delete users']);
        $kepdesRole->givePermissionTo('approve laporan'); // Contoh: Hanya memiliki 1 permission
    }
}
