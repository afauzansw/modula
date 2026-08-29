<?php

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Spatie\Permission\Models\Role;

beforeEach(fn () => $this->seed(DemoContentSeeder::class));

test('it creates the three roles', function () {
    expect(Role::pluck('name')->sort()->values()->all())->toBe(['admin', 'instructor', 'student']);
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

test('running the seeder twice does not duplicate course content', function () {
    $courseCount = Course::count();

    $this->seed(DemoContentSeeder::class);

    expect(Course::count())->toBe($courseCount)
        ->and(User::count())->toBe(4);
});
