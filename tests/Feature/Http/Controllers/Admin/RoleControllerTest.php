<?php

use App\Enums\AdminPermission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function adminUser(): User
{
    $user = createUser();
    $user->givePermissionTo(AdminPermission::Roles->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.roles.index'))->assertRedirect(route('login'));
});

test('a user without the admin.roles permission is forbidden', function () {
    $user = createUser();

    $this->actingAs($user)->get(route('admin.roles.index'))->assertForbidden();
});

test('the index page renders the shell without role data', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.roles.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/roles/index')
            ->missing('roles'),
        );
});

test('fetch returns roles with their permission names as json', function () {
    $role = Role::query()->create(['name' => 'Support', 'guard_name' => 'web', 'is_system' => false]);
    $role->syncPermissions([AdminPermission::Users->value]);

    $data = $this->actingAs(adminUser())
        ->getJson(route('admin.roles.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('name', 'Support'))
        ->toMatchArray(['permissions' => [AdminPermission::Users->value]]);
});

test('fetch filters roles by a partial name match', function () {
    Role::query()->create(['name' => 'Support Team', 'guard_name' => 'web', 'is_system' => false]);
    Role::query()->create(['name' => 'Billing', 'guard_name' => 'web', 'is_system' => false]);

    $data = $this->actingAs(adminUser())
        ->getJson(route('admin.roles.fetch', ['filter' => ['name' => 'supp']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('name')->all())->toBe(['Support Team']);
});

test('fetch sorts roles by name', function () {
    Role::query()->create(['name' => 'Zeta', 'guard_name' => 'web', 'is_system' => false]);
    Role::query()->create(['name' => 'Alpha', 'guard_name' => 'web', 'is_system' => false]);

    $data = $this->actingAs(adminUser())
        ->getJson(route('admin.roles.fetch', ['sort' => 'name']))
        ->assertOk()
        ->json('data');

    $names = collect($data)->pluck('name');

    expect($names->search('Alpha'))->toBeLessThan($names->search('Zeta'));
});

test('fetch filters roles by type', function () {
    Role::query()->create(['name' => 'Support', 'guard_name' => 'web', 'is_system' => false]);
    $user = adminUser();

    $custom = $this->actingAs($user)
        ->getJson(route('admin.roles.fetch', ['filter' => ['is_system' => '0']]))
        ->assertOk()
        ->json('data');

    $system = $this->actingAs($user)
        ->getJson(route('admin.roles.fetch', ['filter' => ['is_system' => '1']]))
        ->assertOk()
        ->json('data');

    expect(collect($custom)->pluck('name')->all())->toBe(['Support'])
        ->and(collect($system)->pluck('is_system')->unique()->all())->toBe([true])
        ->and(collect($system)->pluck('name'))->not->toContain('Support');
});

test('fetch requires the admin.roles permission', function () {
    $this->actingAs(createUser())
        ->getJson(route('admin.roles.fetch'))
        ->assertForbidden();
});

test('guests cannot fetch roles', function () {
    $this->getJson(route('admin.roles.fetch'))->assertUnauthorized();
});

test('permissions returns the admin permission catalogue as json', function () {
    $this->actingAs(adminUser())
        ->getJson(route('admin.roles.permissions'))
        ->assertOk()
        ->assertJsonCount(count(AdminPermission::values()))
        ->assertJsonFragment([AdminPermission::Roles->value => 'Roles & Permissions']);
});

test('permissions requires the admin.roles permission', function () {
    $this->actingAs(createUser())
        ->getJson(route('admin.roles.permissions'))
        ->assertForbidden();
});

test('create and edit have no routes (they are modals on the index)', function () {
    expect(Route::has('admin.roles.create'))->toBeFalse()
        ->and(Route::has('admin.roles.edit'))->toBeFalse();
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

test('bulk destroy deletes every selected custom role', function () {
    $first = Role::query()->create(['name' => 'One', 'guard_name' => 'web', 'is_system' => false]);
    $second = Role::query()->create(['name' => 'Two', 'guard_name' => 'web', 'is_system' => false]);

    $this->actingAs(adminUser())
        ->delete(route('admin.roles.bulk-destroy'), ['ids' => [$first->id, $second->id]])
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::query()->whereIn('id', [$first->id, $second->id])->count())->toBe(0);
});

test('bulk destroy leaves system roles in the selection untouched', function () {
    $custom = Role::query()->create(['name' => 'Temp', 'guard_name' => 'web', 'is_system' => false]);
    $admin = Role::findByName('admin');

    $this->actingAs(adminUser())
        ->delete(route('admin.roles.bulk-destroy'), ['ids' => [$custom->id, $admin->id]])
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::query()->where('id', $custom->id)->exists())->toBeFalse()
        ->and(Role::query()->where('id', $admin->id)->exists())->toBeTrue();
});

test('bulk destroy requires the admin.roles permission', function () {
    $this->actingAs(createUser())
        ->delete(route('admin.roles.bulk-destroy'), ['ids' => [1]])
        ->assertForbidden();
});

test('bulk destroy rejects an empty selection', function () {
    $this->actingAs(adminUser())
        ->delete(route('admin.roles.bulk-destroy'), ['ids' => []])
        ->assertInvalid(['ids']);
});
