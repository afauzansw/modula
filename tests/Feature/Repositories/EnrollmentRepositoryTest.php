<?php

use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use App\Repositories\Eloquent\EloquentEnrollmentRepository;

function enrollmentRepo(): EnrollmentRepositoryInterface
{
    return app(EnrollmentRepositoryInterface::class);
}

test('the enrollment repository interface resolves to the Eloquent implementation', function () {
    expect(enrollmentRepo())->toBeInstanceOf(EloquentEnrollmentRepository::class);
});

test('forStudent() returns only that student enrollments, newest first', function () {
    $student = createUser();

    $older = createEnrollment(['user_id' => $student->id, 'created_at' => now()->subDay()]);
    $newer = createEnrollment(['user_id' => $student->id, 'created_at' => now()]);
    createEnrollment();

    $ids = enrollmentRepo()->forStudent($student->id)->pluck('id')->all();

    expect($ids)->toBe([$newer->id, $older->id]);
});

test('forStudent() eager-loads each course with its instructor', function () {
    $student = createUser();
    createEnrollment(['user_id' => $student->id]);

    $enrollment = enrollmentRepo()->forStudent($student->id)->first();

    expect($enrollment->relationLoaded('course'))->toBeTrue()
        ->and($enrollment->course->relationLoaded('instructor'))->toBeTrue();
});
