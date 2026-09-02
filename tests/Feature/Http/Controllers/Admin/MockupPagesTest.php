<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function userWithPermission(string $permission): User
{
    $user = createUser();
    $user->givePermissionTo($permission);

    return $user;
}

dataset('admin mockup pages', [
    'certificates' => ['admin.certificates.index', AdminPermission::Certificates, 'admin/certificates/index'],
    'students' => ['admin.students.index', AdminPermission::Users, 'admin/students/index'],
    'instructors' => ['admin.instructors.index', AdminPermission::Users, 'admin/instructors/index'],
    'admins' => ['admin.admins.index', AdminPermission::Admins, 'admin/admins/index'],
    'settings' => ['admin.settings', AdminPermission::Settings, 'admin/settings'],
]);

test('guests are redirected to login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('admin mockup pages');

test('a user without the matching permission is forbidden', function (string $routeName) {
    $user = createUser();

    $this->actingAs($user)->get(route($routeName))->assertForbidden();
})->with('admin mockup pages');

test('a user with the matching permission sees the mockup page', function (string $routeName, AdminPermission $permission, string $component) {
    $user = userWithPermission($permission->value);

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with('admin mockup pages');
