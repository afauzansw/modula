<?php

use App\Enums\AdminPermission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Exceptions\SystemRoleException;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function roleRepo(): RoleRepositoryInterface
{
    return app(RoleRepositoryInterface::class);
}

test('the role repository interface resolves to the Eloquent implementation', function () {
    expect(roleRepo())->toBeInstanceOf(EloquentRoleRepository::class);
});

test('createCustomRole() creates a non-system role with the given permissions', function () {
    $role = roleRepo()->createCustomRole('Support', ['admin.users', 'admin.categories']);

    expect($role->is_system)->toBeFalse()
        ->and($role->permissions->pluck('name')->sort()->values()->all())->toBe(['admin.categories', 'admin.users']);
});

test('createCustomRole() rejects a permission outside the catalogue and creates nothing', function () {
    expect(fn () => roleRepo()->createCustomRole('Weird', ['admin.users', 'admin.nope']))
        ->toThrow(InvalidArgumentException::class);

    expect(Role::query()->where('name', 'Weird')->exists())->toBeFalse();
});

test('createCustomRole() rejects a reserved system-role name', function () {
    expect(fn () => roleRepo()->createCustomRole('Admin', []))->toThrow(SystemRoleException::class);
});

test('createCustomRole() rejects a duplicate name', function () {
    roleRepo()->createCustomRole('Support', []);

    expect(fn () => roleRepo()->createCustomRole('Support', []))->toThrow(InvalidArgumentException::class);
});

test('updateCustomRole() renames and re-syncs permissions', function () {
    $role = roleRepo()->createCustomRole('Support', ['admin.users']);

    $updated = roleRepo()->updateCustomRole($role, 'Support Team', ['admin.categories', 'admin.payments']);

    expect($updated->name)->toBe('Support Team')
        ->and($updated->permissions->pluck('name')->sort()->values()->all())->toBe(['admin.categories', 'admin.payments']);
});

test('updateCustomRole() refuses a system role and changes nothing', function () {
    $admin = Role::findByName('admin');

    expect(fn () => roleRepo()->updateCustomRole($admin, 'root', []))->toThrow(SystemRoleException::class);

    expect(Role::findByName('admin')->permissions)->toHaveCount(count(AdminPermission::values()));
});

test('deleteCustomRole() removes a custom role but refuses a system role', function () {
    $custom = roleRepo()->createCustomRole('Temp', []);

    expect(roleRepo()->deleteCustomRole($custom))->toBeTrue()
        ->and(Role::query()->where('name', 'Temp')->exists())->toBeFalse();

    expect(fn () => roleRepo()->deleteCustomRole(Role::findByName('student')))->toThrow(SystemRoleException::class);
    expect(Role::query()->where('name', 'student')->exists())->toBeTrue();
});

test('bulkDelete() deletes custom roles and skips system roles', function () {
    $custom = roleRepo()->createCustomRole('Temp', []);
    $admin = Role::findByName('admin');

    $deleted = roleRepo()->bulkDelete([$custom->id, $admin->id]);

    expect($deleted)->toBe(1)
        ->and(Role::query()->where('name', 'admin')->exists())->toBeTrue()
        ->and(Role::query()->where('name', 'Temp')->exists())->toBeFalse();
});

test('all() filters by is_system and eager-loads permissions', function () {
    roleRepo()->createCustomRole('Support', ['admin.users']);

    $this->app->instance('request', Request::create('/', 'GET', [
        'filter' => ['is_system' => '0'],
        'include' => 'permissions',
    ]));

    $page = roleRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->name)->toBe('Support')
        ->and($page->items()[0]->relationLoaded('permissions'))->toBeTrue();
});
