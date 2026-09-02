<?php

namespace App\Models;

use App\Enums\SystemRole;

/**
 * `User` permanently scoped to the `student` role. The admin student directory
 * reads through this so every query — listing and bulk status change — is
 * constrained without a per-call role filter. It is a query view over the
 * `users` table, not a separate entity.
 */
class Student extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope(
            'role',
            fn ($query) => $query->whereHas('roles', fn ($roles) => $roles->where('name', SystemRole::Student->value)),
        );
    }

    /**
     * Role assignments and every other polymorphic link are stored against
     * `User`, so this view must resolve to the same morph type.
     */
    public function getMorphClass(): string
    {
        return User::class;
    }
}
