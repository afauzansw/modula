<?php

namespace App\Enums;

/**
 * The three built-in roles. They are seeded with `is_system = true` and cannot be
 * renamed or deleted; their names are reserved so custom roles can't shadow them.
 */
enum SystemRole: string
{
    case Admin = 'admin';
    case Instructor = 'instructor';
    case Student = 'student';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public static function isReserved(string $name): bool
    {
        return in_array(mb_strtolower($name), self::values(), true);
    }
}
