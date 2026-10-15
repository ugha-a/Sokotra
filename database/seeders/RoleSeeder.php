<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Create every platform role and permission, granting default permissions.
     * Idempotent, so it can run again from later migrations.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Role::cases() as $role) {
            RoleModel::findOrCreate($role->value, 'web');
        }

        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value, 'web');

            foreach ($permission->defaultRoles() as $role) {
                RoleModel::findByName($role->value, 'web')->givePermissionTo($permission->value);
            }
        }
    }
}
