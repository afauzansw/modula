<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function instructorAdmin(): User
{
    $user = createUser();
    $user->givePermissionTo(AdminPermission::Users->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.instructors.index'))->assertRedirect(route('login'));
});

test('a user without the admin.users permission is forbidden', function () {
    $this->actingAs(createUser())->get(route('admin.instructors.index'))->assertForbidden();
});

test('the index page renders the shell', function () {
    $this->actingAs(instructorAdmin())
        ->get(route('admin.instructors.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/instructors/index'));
});

test('fetch returns only instructors', function () {
    createUserWithRole('instructor', ['name' => 'Ivy']);
    createUserWithRole('student', ['name' => 'Sam']);

    $data = $this->actingAs(instructorAdmin())
        ->getJson(route('admin.instructors.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('name')->all())->toBe(['Ivy']);
});

test('bulk update status blocks the selected instructors', function () {
    $instructor = createUserWithRole('instructor');

    $this->actingAs(instructorAdmin())
        ->patch(route('admin.instructors.bulk-update-status'), [
            'ids' => [$instructor->id],
            'is_blocked' => true,
        ])
        ->assertRedirect(route('admin.instructors.index'));

    expect($instructor->fresh()->is_blocked)->toBeTrue();
});

test('bulk update status cannot block a student through the instructor endpoint', function () {
    $student = createUserWithRole('student');

    $this->actingAs(instructorAdmin())
        ->patch(route('admin.instructors.bulk-update-status'), [
            'ids' => [$student->id],
            'is_blocked' => true,
        ]);

    expect($student->fresh()->is_blocked)->toBeFalse();
});
