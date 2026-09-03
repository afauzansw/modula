<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function adminsAdmin(): User
{
    return createAdmin([AdminPermission::Admins->value]);
}

test('guests are redirected to login', function () {
    $this->get(route('admin.admins.index'))->assertRedirect(route('login'));
});

test('a user without the admin.admins permission is forbidden', function () {
    $this->actingAs(createUser())->get(route('admin.admins.index'))->assertForbidden();
});

test('the index page renders the shell without admin data', function () {
    $this->actingAs(adminsAdmin())
        ->get(route('admin.admins.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/admins/index')
            ->missing('admins'),
        );
});

test('create and edit have no routes (they are modals on the index)', function () {
    expect(Route::has('admin.admins.create'))->toBeFalse()
        ->and(Route::has('admin.admins.edit'))->toBeFalse();
});

test('fetch returns admins with their permission names, excluding non-admins', function () {
    createAdmin([AdminPermission::Courses->value], ['name' => 'Ada Admin']);
    createUserWithRole('student', ['name' => 'Sam Student']);

    $data = $this->actingAs(adminsAdmin())
        ->getJson(route('admin.admins.fetch'))
        ->assertOk()
        ->json('data');

    $ada = collect($data)->firstWhere('name', 'Ada Admin');

    expect(collect($data)->pluck('name'))->not->toContain('Sam Student')
        ->and($ada['permissions'])->toContain(AdminPermission::Courses->value);
});

test('fetch searches by name and filters by email and permission', function () {
    createAdmin([AdminPermission::Courses->value], ['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
    createAdmin([AdminPermission::Users->value], ['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $user = adminsAdmin();

    $byName = $this->actingAs($user)
        ->getJson(route('admin.admins.fetch', ['filter' => ['name' => 'hopper']]))
        ->assertOk()->json('data');

    $byEmail = $this->actingAs($user)
        ->getJson(route('admin.admins.fetch', ['filter' => ['email' => 'ada@']]))
        ->assertOk()->json('data');

    $byPermission = $this->actingAs($user)
        ->getJson(route('admin.admins.fetch', ['filter' => ['permission' => AdminPermission::Courses->value]]))
        ->assertOk()->json('data');

    expect(collect($byName)->pluck('name')->all())->toBe(['Grace Hopper'])
        ->and(collect($byEmail)->pluck('name')->all())->toBe(['Ada Lovelace'])
        ->and(collect($byPermission)->pluck('name')->all())->toBe(['Grace Hopper']);
});

test('store creates a verified admin with the given permissions', function () {
    $this->actingAs(adminsAdmin())
        ->post(route('admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'new@example.com',
            'password' => 'super-secret-123',
            'permissions' => [AdminPermission::Dashboard->value, AdminPermission::Courses->value],
        ])
        ->assertRedirect(route('admin.admins.index'));

    $admin = User::query()->where('email', 'new@example.com')->firstOrFail();

    expect($admin->email_verified_at)->not->toBeNull()
        ->and(Hash::check('super-secret-123', $admin->password))->toBeTrue()
        ->and($admin->getPermissionNames()->sort()->values()->all())
        ->toBe([AdminPermission::Courses->value, AdminPermission::Dashboard->value]);
});

test('store rejects a duplicate email, empty permissions, and an unknown permission', function () {
    $existing = createAdmin();
    $user = adminsAdmin();

    $this->actingAs($user)
        ->post(route('admin.admins.store'), ['name' => 'X', 'email' => $existing->email, 'password' => 'super-secret-123', 'permissions' => ['admin.dashboard']])
        ->assertInvalid(['email']);

    $this->actingAs($user)
        ->post(route('admin.admins.store'), ['name' => 'X', 'email' => 'x@example.com', 'password' => 'super-secret-123', 'permissions' => []])
        ->assertInvalid(['permissions']);

    $this->actingAs($user)
        ->post(route('admin.admins.store'), ['name' => 'X', 'email' => 'x@example.com', 'password' => 'super-secret-123', 'permissions' => ['admin.nope']])
        ->assertInvalid(['permissions.0']);
});

test('update renames an admin and re-syncs permissions, keeping the password when blank', function () {
    $admin = createAdmin([AdminPermission::Dashboard->value], ['name' => 'Old']);
    $original = $admin->password;

    $this->actingAs(adminsAdmin())
        ->put(route('admin.admins.update', $admin), [
            'name' => 'Renamed',
            'email' => $admin->email,
            'permissions' => [AdminPermission::Courses->value],
        ])
        ->assertRedirect(route('admin.admins.index'));

    expect($admin->fresh())
        ->name->toBe('Renamed')
        ->password->toBe($original)
        ->and($admin->fresh()->getPermissionNames()->all())->toBe([AdminPermission::Courses->value]);
});

test('destroy deletes an admin but not your own account', function () {
    $other = createAdmin();
    $me = adminsAdmin();

    $this->actingAs($me)
        ->delete(route('admin.admins.destroy', $other))
        ->assertRedirect(route('admin.admins.index'));

    expect(User::query()->whereKey($other->id)->exists())->toBeFalse();

    $this->actingAs($me)
        ->delete(route('admin.admins.destroy', $me))
        ->assertForbidden();

    expect(User::query()->whereKey($me->id)->exists())->toBeTrue();
});

test('bulk destroy deletes the selected admins and skips your own account', function () {
    $first = createAdmin();
    $second = createAdmin();
    $me = adminsAdmin();

    $this->actingAs($me)
        ->delete(route('admin.admins.bulk-destroy'), ['ids' => [$first->id, $second->id, $me->id]])
        ->assertRedirect(route('admin.admins.index'));

    expect(User::query()->whereIn('id', [$first->id, $second->id])->count())->toBe(0)
        ->and(User::query()->whereKey($me->id)->exists())->toBeTrue();
});

test('bulk destroy rejects an empty selection', function () {
    $this->actingAs(adminsAdmin())
        ->delete(route('admin.admins.bulk-destroy'), ['ids' => []])
        ->assertInvalid(['ids']);
});

test('fetch requires the admin.admins permission', function () {
    $this->actingAs(createUser())->getJson(route('admin.admins.fetch'))->assertForbidden();
});
