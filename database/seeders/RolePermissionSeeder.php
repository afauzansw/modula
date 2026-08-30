<?php

namespace Database\Seeders;

use App\Enums\AdminPermission;
use App\Enums\SystemRole;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Syncs the code-defined admin permission catalogue and the three system roles.
 * Idempotent — safe to re-run on every deploy; picks up new menus, prunes removed
 * ones, and never touches admin-created custom roles.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // The permission cache (config('cache.default')-backed, so it can
        // outlive this process) may predate what's in the table; forget it
        // up front so findOrCreate() below sees the real current state.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->syncPermissions();

        // Creating permissions above can populate the in-process permission
        // cache before all of them exist; forget it now so syncSystemRoles()
        // below sees the full, current catalogue instead of a stale subset.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->syncSystemRoles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function syncPermissions(): void
    {
        foreach (AdminPermission::values() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Drop any admin.* permission that no longer exists in the catalogue.
        Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'admin.%')
            ->whereNotIn('name', AdminPermission::values())
            ->delete();
    }

    private function syncSystemRoles(): void
    {
        foreach (SystemRole::cases() as $systemRole) {
            $role = Role::query()->updateOrCreate(
                ['name' => $systemRole->value, 'guard_name' => 'web'],
                ['is_system' => true],
            );

            $role->syncPermissions(
                $systemRole === SystemRole::Admin ? AdminPermission::values() : [],
            );
        }
    }
}
