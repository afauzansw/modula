<?php

use App\Enums\AdminPermission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function adminUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(AdminPermission::Roles->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.roles.index'))->assertRedirect(route('login'));
});

test('a user without the admin.roles permission is forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.roles.index'))->assertForbidden();
});

test('the index page lists roles with their permission names', function () {
    $user = adminUser();
    $role = Role::query()->create(['name' => 'Support', 'guard_name' => 'web', 'is_system' => false]);
    $role->syncPermissions([AdminPermission::Users->value]);

    $this->actingAs($user)
        ->get(route('admin.roles.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/roles/index')
            ->where('roles.data', fn ($data) => collect($data)->contains(
                fn ($item) => $item['name'] === 'Support' && $item['permissions'] === [AdminPermission::Users->value],
            )),
        );
});

test('the create page is displayed', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.roles.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/roles/create')
            ->has('permissions'),
        );
});

test('store creates a custom role with the given permissions', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.roles.store'), [
            'name' => 'Support',
            'permissions' => [AdminPermission::Users->value, AdminPermission::Categories->value],
        ])
        ->assertRedirect(route('admin.roles.index'));

    $role = Role::query()->where('name', 'Support')->firstOrFail();

    expect($role->is_system)->toBeFalse()
        ->and($role->permissions->pluck('name')->sort()->values()->all())
        ->toBe([AdminPermission::Categories->value, AdminPermission::Users->value]);
});

test('store rejects a reserved system-role name', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.roles.store'), ['name' => 'admin', 'permissions' => []])
        ->assertInvalid(['name']);
});

test('store rejects a permission outside the catalogue', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.roles.store'), ['name' => 'Support', 'permissions' => ['admin.nope']])
        ->assertInvalid(['permissions.0']);
});

test('store rejects a duplicate role name', function () {
    Role::query()->create(['name' => 'Support', 'guard_name' => 'web', 'is_system' => false]);

    $this->actingAs(adminUser())
        ->post(route('admin.roles.store'), ['name' => 'Support', 'permissions' => []])
        ->assertInvalid(['name']);
});

test('editing a system role is forbidden', function () {
    $admin = Role::findByName('admin');

    $this->actingAs(adminUser())
        ->get(route('admin.roles.edit', $admin))
        ->assertForbidden();
});

test('update renames a custom role and re-syncs its permissions', function () {
    $role = Role::query()->create(['name' => 'Support', 'guard_name' => 'web', 'is_system' => false]);
    $role->syncPermissions([AdminPermission::Users->value]);

    $this->actingAs(adminUser())
        ->put(route('admin.roles.update', $role), [
            'name' => 'Support Team',
            'permissions' => [AdminPermission::Categories->value],
        ])
        ->assertRedirect(route('admin.roles.index'));

    $role->refresh();

    expect($role->name)->toBe('Support Team')
        ->and($role->permissions->pluck('name')->all())->toBe([AdminPermission::Categories->value]);
});

test('updating a system role is forbidden', function () {
    $admin = Role::findByName('admin');

    $this->actingAs(adminUser())
        ->put(route('admin.roles.update', $admin), ['name' => 'root', 'permissions' => []])
        ->assertForbidden();

    expect(Role::findByName('admin')->permissions)->toHaveCount(count(AdminPermission::values()));
});

test('destroy deletes a custom role', function () {
    $role = Role::query()->create(['name' => 'Temp', 'guard_name' => 'web', 'is_system' => false]);

    $this->actingAs(adminUser())
        ->delete(route('admin.roles.destroy', $role))
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::query()->where('name', 'Temp')->exists())->toBeFalse();
});

test('destroying a system role is forbidden', function () {
    $student = Role::findByName('student');

    $this->actingAs(adminUser())
        ->delete(route('admin.roles.destroy', $student))
        ->assertForbidden();

    expect(Role::query()->where('name', 'student')->exists())->toBeTrue();
});
