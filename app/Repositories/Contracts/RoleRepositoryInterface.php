<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use App\Repositories\Exceptions\SystemRoleException;

/**
 * Role management for the admin dashboard. Inherits the base listing/CRUD
 * (`all()` supports `?filter[is_system]=0`; `permissions` are always eager-loaded)
 * and adds the custom-role operations. The three system roles are never writable
 * through here.
 */
interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Create an admin-defined role holding a subset of the AdminPermission catalogue.
     *
     * @param  list<string>  $permissionNames
     *
     * @throws SystemRoleException when $name is a reserved system-role name
     * @throws \InvalidArgumentException when a permission name is outside the catalogue
     */
    public function createCustomRole(string $name, array $permissionNames): Role;

    /**
     * Rename (optional) and re-sync the permissions of a custom role.
     *
     * @param  list<string>  $permissionNames
     *
     * @throws SystemRoleException when $role is a system role
     * @throws \InvalidArgumentException when a permission name is outside the catalogue
     */
    public function updateCustomRole(Role $role, ?string $name, array $permissionNames): Role;

    /**
     * @throws SystemRoleException when $role is a system role
     */
    public function deleteCustomRole(Role $role): bool;
}
