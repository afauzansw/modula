<?php

use App\Models\Order;
use Illuminate\Database\UniqueConstraintViolationException;

test('a user cannot hold two active orders for the same course', function () {
    $user = createUser();
    $course = createCourse();

    createOrder(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'pending']);

    expect(fn () => createOrder(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'paid']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('a user can start a new order once the previous one has failed or expired', function () {
    $user = createUser();
    $course = createCourse();

    createOrder(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'failed']);
    createOrder(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'expired']);
    createOrder(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'pending']);

    expect(Order::where('user_id', $user->id)->where('course_id', $course->id)->count())->toBe(3);
});

test('the active-order constraint is scoped per course', function () {
    $user = createUser();
    $courseA = createCourse();
    $courseB = createCourse();

    createOrder(['user_id' => $user->id, 'course_id' => $courseA->id, 'status' => 'pending']);
    createOrder(['user_id' => $user->id, 'course_id' => $courseB->id, 'status' => 'pending']);

    expect(Order::where('user_id', $user->id)->count())->toBe(2);
});
