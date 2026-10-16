<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

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

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $operasional = Role::findOrCreate('admin_operasional', 'web');
        $petugas = Role::findOrCreate('petugas', 'web');

        $superAdmin->syncPermissions($permissions);

        $operasional->syncPermissions([
            'view shipments',
            'create shipments',
            'update shipments',
            'manage tracking',
            'manage tariffs',
        ]);

        $petugas->syncPermissions([
            'view shipments',
            'manage tracking',
        ]);
    }
}
