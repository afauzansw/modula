<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Spatie role + the `is_system` flag that protects the three built-in roles
 * (`admin`, `instructor`, `student`) from being renamed or deleted.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Role extends SpatieRole
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'is_system' => 'boolean',
        ]);
    }
}
