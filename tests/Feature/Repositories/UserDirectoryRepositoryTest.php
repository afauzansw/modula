<?php

use App\Repositories\Contracts\InstructorRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Eloquent\EloquentInstructorRepository;
use App\Repositories\Eloquent\EloquentStudentRepository;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function studentRepo(): StudentRepositoryInterface
{
    return app(StudentRepositoryInterface::class);
}

function instructorRepo(): InstructorRepositoryInterface
{
    return app(InstructorRepositoryInterface::class);
}

test('the interfaces resolve to their Eloquent implementations', function () {
    expect(studentRepo())->toBeInstanceOf(EloquentStudentRepository::class)
        ->and(instructorRepo())->toBeInstanceOf(EloquentInstructorRepository::class);
});

test('the student repository lists only users with the student role', function () {
    createUserWithRole('student', ['name' => 'Sam Student']);
    createUserWithRole('instructor', ['name' => 'Ivy Instructor']);

    expect(collect(studentRepo()->all()->items())->pluck('name')->all())->toBe(['Sam Student']);
});

test('the instructor repository lists only users with the instructor role', function () {
    createUserWithRole('student', ['name' => 'Sam Student']);
    createUserWithRole('instructor', ['name' => 'Ivy Instructor']);

    expect(collect(instructorRepo()->all()->items())->pluck('name')->all())->toBe(['Ivy Instructor']);
});

test('all() searches by name or email, case-insensitively', function () {
    createUserWithRole('student', ['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
    createUserWithRole('student', ['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['search' => 'hopper']]));
    expect(collect(studentRepo()->all()->items())->pluck('name')->all())->toBe(['Grace Hopper']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['search' => 'ADA@EXAMPLE']]));
    expect(collect(studentRepo()->all()->items())->pluck('name')->all())->toBe(['Ada Lovelace']);
});

test('bulkUpdate() only touches users of the repository role', function () {
    $student = createUserWithRole('student');
    $instructor = createUserWithRole('instructor');

    $updated = studentRepo()->bulkUpdate([$student->id, $instructor->id], ['is_blocked' => true]);

    expect($updated)->toBe(1)
        ->and($student->fresh()->is_blocked)->toBeTrue()
        ->and($instructor->fresh()->is_blocked)->toBeFalse();
});
