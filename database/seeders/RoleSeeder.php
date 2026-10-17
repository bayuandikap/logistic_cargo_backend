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

            'view branches',
            'create branches',
            'update branches',
            'delete branches',

            'view tariffs',
            'create tariffs',
            'update tariffs',
            'delete tariffs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Buat role.
        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $operasional = Role::findOrCreate('admin_operasional', 'web');
        $petugas = Role::findOrCreate('petugas', 'web');

        // Super admin: semua permission.
        $superAdmin->syncPermissions($permissions);

        // Admin operasional: boleh melihat, membuat, dan mengubah tarif.
        $operasional->syncPermissions([
            'view shipments',
            'create shipments',
            'update shipments',
            'manage tracking',
            'manage tariffs',
            'view branches',
            'view tariffs',
            'create tariffs',
            'update tariffs',
        ]);

        // Petugas: tidak mendapat akses ke tarif.
        $petugas->syncPermissions([
            'view shipments',
            'manage tracking',
            'view branches',
        ]);

        // Bersihkan cache setelah perubahan.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
