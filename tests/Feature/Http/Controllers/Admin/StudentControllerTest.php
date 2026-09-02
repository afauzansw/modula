<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function studentAdmin(): User
{
    $user = createUser();
    $user->givePermissionTo(AdminPermission::Users->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.students.index'))->assertRedirect(route('login'));
});

test('a user without the admin.users permission is forbidden', function () {
    $this->actingAs(createUser())->get(route('admin.students.index'))->assertForbidden();
});

test('the index page renders the shell without data', function () {
    $this->actingAs(studentAdmin())
        ->get(route('admin.students.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/students/index')
            ->missing('students'),
        );
});

test('create and edit have no routes', function () {
    expect(Route::has('admin.students.create'))->toBeFalse()
        ->and(Route::has('admin.students.edit'))->toBeFalse();
});

test('fetch returns only students, with their status', function () {
    createUserWithRole('student', ['name' => 'Sam']);
    createUserWithRole('instructor', ['name' => 'Ivy']);

    $data = $this->actingAs(studentAdmin())
        ->getJson(route('admin.students.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('name')->all())->toBe(['Sam'])
        ->and($data[0])->toHaveKeys(['id', 'name', 'email', 'is_blocked', 'created_at']);
});

test('fetch searches students by name or email', function () {
    createUserWithRole('student', ['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
    createUserWithRole('student', ['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $data = $this->actingAs(studentAdmin())
        ->getJson(route('admin.students.fetch', ['filter' => ['search' => 'lovelace']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('name')->all())->toBe(['Ada Lovelace']);
});

test('bulk update status blocks the selected students', function () {
    $first = createUserWithRole('student');
    $second = createUserWithRole('student');

    $this->actingAs(studentAdmin())
        ->patch(route('admin.students.bulk-update-status'), [
            'ids' => [$first->id, $second->id],
            'is_blocked' => true,
        ])
        ->assertRedirect(route('admin.students.index'));

    expect($first->fresh()->is_blocked)->toBeTrue()
        ->and($second->fresh()->is_blocked)->toBeTrue();
});

test('bulk update status unblocks the selected students', function () {
    $student = createUserWithRole('student');
    User::query()->whereKey($student->id)->update(['is_blocked' => true]);

    $this->actingAs(studentAdmin())
        ->patch(route('admin.students.bulk-update-status'), [
            'ids' => [$student->id],
            'is_blocked' => false,
        ])
        ->assertRedirect(route('admin.students.index'));

    expect($student->fresh()->is_blocked)->toBeFalse();
});

test('bulk update status cannot block an instructor through the student endpoint', function () {
    $instructor = createUserWithRole('instructor');

    $this->actingAs(studentAdmin())
        ->patch(route('admin.students.bulk-update-status'), [
            'ids' => [$instructor->id],
            'is_blocked' => true,
        ]);

    expect($instructor->fresh()->is_blocked)->toBeFalse();
});

test('bulk update status rejects an empty selection or a missing flag', function () {
    $this->actingAs(studentAdmin())
        ->patch(route('admin.students.bulk-update-status'), ['ids' => [], 'is_blocked' => true])
        ->assertInvalid(['ids']);

    $student = createUserWithRole('student');

    $this->actingAs(studentAdmin())
        ->patch(route('admin.students.bulk-update-status'), ['ids' => [$student->id]])
        ->assertInvalid(['is_blocked']);
});

test('fetch requires the admin.users permission', function () {
    $this->actingAs(createUser())->getJson(route('admin.students.fetch'))->assertForbidden();
});

test('bulk update status requires the admin.users permission', function () {
    $this->actingAs(createUser())
        ->patch(route('admin.students.bulk-update-status'), ['ids' => [1], 'is_blocked' => true])
        ->assertForbidden();
});
