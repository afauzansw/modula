<?php

namespace App\Repositories\Exceptions;

use App\Models\Role;
use RuntimeException;

/**
 * Thrown when a write is attempted against one of the built-in system roles
 * (`admin`, `instructor`, `student`). A later HTTP slice can map this to a 422.
 */
class SystemRoleException extends RuntimeException
{
    public static function cannotModify(Role $role): self
    {
        return new self("The '{$role->name}' role is a system role and cannot be modified or deleted.");
    }

    public static function nameReserved(string $name): self
    {
        return new self("'{$name}' is a reserved system-role name.");
    }
}
