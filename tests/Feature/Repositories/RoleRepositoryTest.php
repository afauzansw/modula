<?php

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Eloquent\EloquentRoleRepository;
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

test('create() persists a custom role and syncs the given permissions', function () {
    roleRepo()->create(['name' => 'Support', 'permissions' => ['admin.users', 'admin.categories']]);

    $role = Role::findByName('Support');

    expect($role->is_system)->toBeFalse()
        ->and($role->guard_name)->toBe('web')
        ->and($role->permissions->pluck('name')->sort()->values()->all())->toBe(['admin.categories', 'admin.users']);
});

test('create() defaults to no permissions when the key is absent', function () {
    roleRepo()->create(['name' => 'Bare']);

    expect(Role::findByName('Bare')->permissions)->toBeEmpty();
});

test('update() renames the role and re-syncs its permissions', function () {
    $role = roleRepo()->create(['name' => 'Support', 'permissions' => ['admin.users']]);

    roleRepo()->update($role, ['name' => 'Support Team', 'permissions' => ['admin.categories']]);

    expect($role->refresh()->name)->toBe('Support Team')
        ->and($role->permissions->pluck('name')->all())->toBe(['admin.categories']);
});

test('update() leaves permissions untouched when the key is absent', function () {
    $role = roleRepo()->create(['name' => 'Support', 'permissions' => ['admin.users']]);

    roleRepo()->update($role, ['name' => 'Renamed']);

    expect($role->refresh()->permissions->pluck('name')->all())->toBe(['admin.users']);
});

test('all() filters by is_system and eager-loads permissions', function () {
    $support = Role::query()->create(['name' => 'Support', 'guard_name' => 'web', 'is_system' => false]);
    $support->syncPermissions(['admin.users']);

    $this->app->instance('request', Request::create('/', 'GET', [
        'filter' => ['is_system' => '0'],
        'include' => 'permissions',
    ]));

    $page = roleRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->name)->toBe('Support')
        ->and($page->items()[0]->relationLoaded('permissions'))->toBeTrue();
});
