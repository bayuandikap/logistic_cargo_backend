<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(
            empty($tableNames),
            new RuntimeException('Error: permission configuration is missing. Run [php artisan config:clear] and try again.')
        );

        /*
         * Permissions
         */
        if (! Schema::hasTable($tableNames['permissions'])) {
            Schema::create($tableNames['permissions'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });
        }

        /*
         * Roles
         */
        if (! Schema::hasTable($tableNames['roles'])) {
            Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
                $table->bigIncrements('id');

                if ($teams || config('permission.testing')) {
                    $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

                    $table->unsignedBigInteger($teamKey)->nullable();
                    $table->index($teamKey, 'roles_team_foreign_key_index');
                }

                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();

                if ($teams || config('permission.testing')) {
                    $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

                    $table->unique(
                        [$teamKey, 'name', 'guard_name'],
                        'roles_team_name_guard_name_unique'
                    );
                } else {
                    $table->unique(['name', 'guard_name']);
                }
            });
        }

        /*
         * Model has permissions
         */
        if (! Schema::hasTable($tableNames['model_has_permissions'])) {
            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
                $table->unsignedBigInteger($pivotPermission);

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key'] ?? 'model_id');
                $table->index(
                    [$columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                    'model_has_permissions_model_id_model_type_index'
                );

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                if ($teams) {
                    $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

                    $table->unsignedBigInteger($teamKey);
                    $table->index($teamKey, 'model_has_permissions_team_foreign_key_index');

                    $table->primary(
                        [$teamKey, $pivotPermission, $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                        'model_has_permissions_permission_model_type_primary'
                    );
                } else {
                    $table->primary(
                        [$pivotPermission, $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                        'model_has_permissions_permission_model_type_primary'
                    );
                }
            });
        }

        /*
         * Model has roles
         */
        if (! Schema::hasTable($tableNames['model_has_roles'])) {
            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
                $table->unsignedBigInteger($pivotRole);

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key'] ?? 'model_id');
                $table->index(
                    [$columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                    'model_has_roles_model_id_model_type_index'
                );

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                if ($teams) {
                    $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

                    $table->unsignedBigInteger($teamKey);
                    $table->index($teamKey, 'model_has_roles_team_foreign_key_index');

                    $table->primary(
                        [$teamKey, $pivotRole, $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                        'model_has_roles_role_model_type_primary'
                    );
                } else {
                    $table->primary(
                        [$pivotRole, $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                        'model_has_roles_role_model_type_primary'
                    );
                }
            });
        }

        /*
         * Role has permissions
         */
        if (! Schema::hasTable($tableNames['role_has_permissions'])) {
            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
                $table->unsignedBigInteger($pivotPermission);
                $table->unsignedBigInteger($pivotRole);

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(
                    [$pivotPermission, $pivotRole],
                    'role_has_permissions_permission_id_role_id_primary'
                );
            });
        }

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            return;
        }

        /*
         * Drop pivot tables before their referenced tables.
         */
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
