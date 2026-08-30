<?php

use App\Enums\AdminPermission;
use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('it seeds the three system roles as protected', function () {
    $roles = Role::query()->where('is_system', true)->orderBy('name')->pluck('name');

    expect($roles->all())->toBe(['admin', 'instructor', 'student']);
});

test('it seeds every catalogue permission on the web guard', function () {
    $names = Permission::query()->where('guard_name', 'web')->orderBy('name')->pluck('name');

    expect($names->all())->toBe(collect(AdminPermission::values())->sort()->values()->all());
});

test('admin holds every permission; instructor and student hold none', function () {
    expect(Role::findByName('admin')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(collect(AdminPermission::values())->sort()->values()->all())
        ->and(Role::findByName('instructor')->permissions)->toHaveCount(0)
        ->and(Role::findByName('student')->permissions)->toHaveCount(0);
});

test('re-running the seeder does not duplicate roles or permissions', function () {
    $roles = Role::query()->count();
    $permissions = Permission::query()->count();

    $this->seed(RolePermissionSeeder::class);

    expect(Role::query()->count())->toBe($roles)
        ->and(Permission::query()->count())->toBe($permissions);
});

test('re-running the seeder prunes an admin permission no longer in the catalogue', function () {
    Permission::findOrCreate('admin.legacy', 'web');

    $this->seed(RolePermissionSeeder::class);

    expect(Permission::query()->where('name', 'admin.legacy')->exists())->toBeFalse();
});

test('re-running the seeder does not touch admin-created custom roles', function () {
    $custom = Role::create(['name' => 'Support', 'guard_name' => 'web']);
    $custom->givePermissionTo('admin.users');

    $this->seed(RolePermissionSeeder::class);

    expect(Role::query()->where('name', 'Support')->exists())->toBeTrue()
        ->and($custom->fresh()->is_system)->toBeFalse()
        ->and($custom->fresh()->permissions->pluck('name')->all())->toBe(['admin.users']);
});
