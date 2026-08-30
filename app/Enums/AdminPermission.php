<?php

namespace App\Enums;

/**
 * The canonical catalogue of admin-dashboard permissions — one case per menu.
 *
 * Permissions are code-defined, not admin-editable. `RolePermissionSeeder` syncs
 * these into the `permissions` table (guard `web`) and prunes anything stale.
 * Admins compose custom roles from this list; the `admin` system role holds all of it.
 */
enum AdminPermission: string
{
    case Dashboard = 'admin.dashboard';
    case Users = 'admin.users';
    case Roles = 'admin.roles';
    case Categories = 'admin.categories';
    case Courses = 'admin.courses';
    case Enrollments = 'admin.enrollments';
    case Orders = 'admin.orders';
    case Payments = 'admin.payments';
    case Certificates = 'admin.certificates';
    case Ratings = 'admin.ratings';

    /**
     * Human-readable menu label for the future assign-permissions UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard',
            self::Users => 'Users',
            self::Roles => 'Roles & Permissions',
            self::Categories => 'Categories',
            self::Courses => 'Courses',
            self::Enrollments => 'Enrollments',
            self::Orders => 'Orders',
            self::Payments => 'Payments',
            self::Certificates => 'Certificates',
            self::Ratings => 'Ratings & Reviews',
        };
    }

    /**
     * Every permission name.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Permission name => label, for rendering the permission picker.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }
}
