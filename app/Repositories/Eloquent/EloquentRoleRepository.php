<?php

namespace App\Repositories\Eloquent;

use App\Enums\AdminPermission;
use App\Enums\SystemRole;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Exceptions\SystemRoleException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Permission\PermissionRegistrar;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Role management for the admin dashboard. Inherits the base CRUD/listing from
 * BaseRepository and adds the custom-role operations, all of which refuse to
 * touch a system role.
 */
class EloquentRoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = ['name'];

    /** @var list<string> */
    protected array $allowedSorts = ['name', 'created_at'];

    /** @var list<string> */
    protected array $allowedIncludes = ['permissions'];

    /** @var list<string> */
    protected array $with = ['permissions'];

    public function __construct(Role $model)
    {
        parent::__construct($model);

        // Cast the query-string value to a real bool so it compares cleanly
        // against the boolean column on any driver.
        $this->allowedFilters[] = AllowedFilter::callback(
            'is_system',
            fn ($query, $value) => $query->where('is_system', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
        );
    }

    public function createCustomRole(string $name, array $permissionNames): Role
    {
        if (SystemRole::isReserved($name)) {
            throw SystemRoleException::nameReserved($name);
        }

        $permissions = $this->validatedPermissions($permissionNames);

        return DB::transaction(function () use ($name, $permissions): Role {
            if (Role::query()->where('name', $name)->where('guard_name', 'web')->exists()) {
                throw new InvalidArgumentException("A role named '{$name}' already exists.");
            }

            $role = Role::query()->create([
                'name' => $name,
                'guard_name' => 'web',
                'is_system' => false,
            ]);

            $role->syncPermissions($permissions);
            $this->forgetPermissionCache();

            return $role;
        });
    }

    public function updateCustomRole(Role $role, ?string $name, array $permissionNames): Role
    {
        $this->guardSystemRole($role);

        $permissions = $this->validatedPermissions($permissionNames);

        return DB::transaction(function () use ($role, $name, $permissions): Role {
            if ($name !== null && $name !== $role->name) {
                if (SystemRole::isReserved($name)) {
                    throw SystemRoleException::nameReserved($name);
                }

                $role->update(['name' => $name]);
            }

            $role->syncPermissions($permissions);
            $this->forgetPermissionCache();

            return $role->refresh();
        });
    }

    public function deleteCustomRole(Role $role): bool
    {
        $this->guardSystemRole($role);

        return DB::transaction(function () use ($role): bool {
            $deleted = $role->delete() ?? false;
            $this->forgetPermissionCache();

            return $deleted;
        });
    }

    /**
     * The inherited bulk delete would happily wipe system roles — this one skips them.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $deleted = $this->model->newQuery()
                ->where('is_system', false)
                ->whereIn('id', $ids)
                ->delete();

            $this->forgetPermissionCache();

            return $deleted;
        });
    }

    private function guardSystemRole(Role $role): void
    {
        if ($role->is_system) {
            throw SystemRoleException::cannotModify($role);
        }
    }

    /**
     * @param  list<string>  $permissionNames
     * @return list<string>
     */
    private function validatedPermissions(array $permissionNames): array
    {
        $unknown = array_diff($permissionNames, AdminPermission::values());

        if ($unknown !== []) {
            throw new InvalidArgumentException(
                'Unknown permission(s): '.implode(', ', $unknown).'.',
            );
        }

        return array_values(array_unique($permissionNames));
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
