<?php

namespace App\Models;

use App\Enums\AdminPermission;
use Illuminate\Support\Collection;
use Spatie\Permission\Guard;

/**
 * `User` permanently scoped to accounts that hold at least one direct
 * admin-panel permission (`AdminPermission`). The admin-management screen reads
 * and writes through this. It is a query view over the `users` table, not a
 * separate entity.
 */
class Admin extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('admin', fn ($query) => $query->whereHas(
            'permissions',
            fn ($permissions) => $permissions->whereIn('name', AdminPermission::values()),
        ));
    }

    /**
     * Permission and role links are stored against `User`, so this view must
     * resolve to the same morph type.
     */
    public function getMorphClass(): string
    {
        return User::class;
    }

    /**
     * `Admin` isn't a configured auth model, so Spatie can't derive its guard —
     * borrow `User`'s (both back the `users` table / `web` guard).
     *
     * @return Collection<int, string>
     */
    protected function getGuardNames(): Collection
    {
        return Guard::getNames(User::class);
    }
}
