<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $permissions = [
            // Branches
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',

            // Tariffs
            'view tariffs',
            'create tariffs',
            'edit tariffs',
            'delete tariffs',

            // Shipments
            'view shipments',
            'create shipments',
            'edit shipments',
            'delete shipments',

            // Tracking checkpoints
            'view tracking checkpoints',
            'create tracking checkpoints',
            'edit tracking checkpoints',
            'delete tracking checkpoints',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => $guard,
        ]);

        $adminOperasional = Role::firstOrCreate([
            'name' => 'admin_operasional',
            'guard_name' => $guard,
        ]);

        $petugas = Role::firstOrCreate([
            'name' => 'petugas',
            'guard_name' => $guard,
        ]);

        // Super admin receives every defined permission.
        $superAdmin->syncPermissions($permissions);

        // Operational admin: manage shipments and tracking,
        // view branches and tariffs.
        $adminOperasional->syncPermissions([
            'view branches',
            'view tariffs',

            'view shipments',
            'create shipments',
            'edit shipments',
            'delete shipments',

            'view tracking checkpoints',
            'create tracking checkpoints',
            'edit tracking checkpoints',
            'delete tracking checkpoints',
        ]);

        // Petugas: view shipments and manage tracking updates.
        $petugas->syncPermissions([
            'view shipments',
            'view tracking checkpoints',
            'create tracking checkpoints',
            'edit tracking checkpoints',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
