<?php

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Rating;
use App\Models\Role;
use App\Models\User;

beforeEach(fn () => $this->seed());

test('it seeds the three protected system roles', function () {
    $roles = Role::query()->orderBy('name')->get();

    expect($roles->pluck('name')->all())->toBe(['admin', 'instructor', 'student'])
        ->and($roles->every(fn (Role $role) => $role->is_system))->toBeTrue();
});

test('it seeds the demo accounts with the right roles, all verified', function () {
    expect(User::count())->toBe(5)
        ->and(User::role('instructor')->count())->toBe(1)
        ->and(User::role('student')->count())->toBe(2)
        ->and(User::role('admin')->count())->toBe(1)
        ->and(User::whereNull('email_verified_at')->count())->toBe(0);
});

test('the demo accounts can log in with the shared password', function () {
    $this->post(route('login.store'), ['email' => 'admin@example.com', 'password' => 'password'])
        ->assertRedirect();

    $this->assertAuthenticated();
});

test('it builds a free and a paid published course owned by the instructor', function () {
    $instructor = User::role('instructor')->sole();

    $courses = Course::query()->where('instructor_id', $instructor->id)->get();

    expect($courses)->toHaveCount(2)
        ->and($courses->pluck('status')->unique()->all())->toBe(['published'])
        ->and($courses->firstWhere('is_free', true))->not->toBeNull()
        ->and($courses->firstWhere('is_free', false)->price)->toBeGreaterThan(0);
});

test('each course has modules with lessons, quizzes and an assignment', function () {
    $course = Course::query()->where('is_free', false)->firstOrFail();

    expect($course->modules()->count())->toBe(3)
        ->and($course->lessons()->count())->toBe(10)
        ->and($course->lessons()->where('type', 'quiz')->count())->toBe(3)
        ->and($course->lessons()->where('type', 'assignment')->count())->toBe(1);
});

test('the completed student has a 100% enrollment, a paid order and a certificate', function () {
    $course = Course::query()->where('is_free', false)->firstOrFail();

    $enrollment = Enrollment::query()->where('status', 'completed')->sole();

    expect($enrollment->course_id)->toBe($course->id)
        ->and($enrollment->progress_percent)->toBe(100)
        ->and($enrollment->last_lesson_id)->not->toBeNull()
        ->and(Order::query()->where('user_id', $enrollment->user_id)->where('course_id', $course->id)->where('status', 'paid')->exists())->toBeTrue()
        ->and(Certificate::query()->where('user_id', $enrollment->user_id)->where('course_id', $course->id)->exists())->toBeTrue();
});

test('each enrolled student has a rating snapshotting their enrollment progress', function () {
    expect(Rating::count())->toBe(2);

    Rating::all()->each(function (Rating $rating) {
        $enrollment = Enrollment::query()
            ->where('user_id', $rating->user_id)
            ->where('course_id', $rating->course_id)
            ->sole();

        expect($rating->progress_percent_at_review)->toBe($enrollment->progress_percent)
            ->and($rating->last_lesson_id_at_review)->toBe($enrollment->last_lesson_id);
    });
});

test('running the seeder twice does not duplicate anything', function () {
    $courseCount = Course::count();
    $userCount = User::count();
    $ratingCount = Rating::count();

    $this->seed();

    expect(Course::count())->toBe($courseCount)
        ->and(User::count())->toBe($userCount)
        ->and(Rating::count())->toBe($ratingCount);
});
