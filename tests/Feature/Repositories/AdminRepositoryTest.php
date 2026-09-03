<?php

use App\Models\User;
use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Repositories\Eloquent\EloquentAdminRepository;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function adminRepo(): AdminRepositoryInterface
{
    return app(AdminRepositoryInterface::class);
}

test('the admin repository interface resolves to the Eloquent implementation', function () {
    expect(adminRepo())->toBeInstanceOf(EloquentAdminRepository::class);
});

test('all() lists only users holding an admin-panel permission', function () {
    createAdmin(['admin.dashboard'], ['name' => 'Ada Admin']);
    createUser(['name' => 'Plain User']);
    createUserWithRole('student', ['name' => 'Sam Student']);

    expect(collect(adminRepo()->all()->items())->pluck('name')->all())->toBe(['Ada Admin']);
});

test('all() searches by name and filters by email and permission', function () {
    createAdmin(['admin.courses'], ['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
    createAdmin(['admin.users'], ['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['name' => 'hopper']]));
    expect(collect(adminRepo()->all()->items())->pluck('name')->all())->toBe(['Grace Hopper']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['email' => 'ada@']]));
    expect(collect(adminRepo()->all()->items())->pluck('name')->all())->toBe(['Ada Lovelace']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['permission' => 'admin.courses']]));
    expect(collect(adminRepo()->all()->items())->pluck('name')->all())->toBe(['Grace Hopper']);
});

test('create() makes a verified admin with the given permissions', function () {
    adminRepo()->create([
        'name' => 'New Admin',
        'email' => 'new@example.com',
        'password' => 'super-secret-123',
        'permissions' => ['admin.dashboard', 'admin.courses'],
    ]);

    $admin = User::query()->where('email', 'new@example.com')->firstOrFail();

    expect($admin->email_verified_at)->not->toBeNull()
        ->and(Hash::check('super-secret-123', $admin->password))->toBeTrue()
        ->and($admin->getPermissionNames()->sort()->values()->all())->toBe(['admin.courses', 'admin.dashboard']);
});

test('update() renames the account and re-syncs its permissions', function () {
    $admin = createAdmin(['admin.dashboard'], ['name' => 'Old', 'email' => 'old@example.com']);

    adminRepo()->update($admin, [
        'name' => 'New',
        'email' => 'old@example.com',
        'permissions' => ['admin.courses'],
    ]);

    expect($admin->fresh()->name)->toBe('New')
        ->and($admin->fresh()->getPermissionNames()->all())->toBe(['admin.courses']);
});

test('update() keeps the current password when none is given', function () {
    $admin = createAdmin();
    $original = $admin->password;

    adminRepo()->update($admin, [
        'name' => $admin->name,
        'email' => $admin->email,
        'password' => null,
        'permissions' => ['admin.dashboard'],
    ]);

    expect($admin->fresh()->password)->toBe($original);
});

test('bulkDelete() only removes admin users', function () {
    $admin = createAdmin();
    $student = createUserWithRole('student');

    $deleted = adminRepo()->bulkDelete([$admin->id, $student->id]);

    expect($deleted)->toBe(1)
        ->and(User::query()->whereKey($admin->id)->exists())->toBeFalse()
        ->and(User::query()->whereKey($student->id)->exists())->toBeTrue();
});
