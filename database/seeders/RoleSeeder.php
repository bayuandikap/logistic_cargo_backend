<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan cache permission.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Buat seluruh permission terlebih dahulu.
        $permissions = [
            'view shipments',
            'create shipments',
            'update shipments',
            'delete shipments',
            'manage tracking',
            'manage tariffs',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Buat role.
        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $operasional = Role::findOrCreate('admin_operasional', 'web');
        $petugas = Role::findOrCreate('petugas', 'web');

        // Super admin mendapat semua permission.
        $superAdmin->syncPermissions($permissions);

        // Admin operasional mengelola pengiriman dan tarif.
        $operasional->syncPermissions([
            'view shipments',
            'create shipments',
            'update shipments',
            'manage tracking',
            'manage tariffs',
        ]);

        // Petugas dapat melihat pengiriman dan mengelola tracking.
        $petugas->syncPermissions([
            'view shipments',
            'manage tracking',
        ]);

        // Bersihkan cache setelah perubahan.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
