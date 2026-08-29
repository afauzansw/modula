<?php

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

test('a user cannot hold two active orders for the same course', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();

    Order::factory()->for($user, 'user')->for($course)->create(['status' => 'pending']);

    expect(fn () => Order::factory()->for($user, 'user')->for($course)->create(['status' => 'paid']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('a user can start a new order once the previous one has failed or expired', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();

    Order::factory()->for($user, 'user')->for($course)->create(['status' => 'failed']);
    Order::factory()->for($user, 'user')->for($course)->create(['status' => 'expired']);
    Order::factory()->for($user, 'user')->for($course)->create(['status' => 'pending']);

    expect(Order::where('user_id', $user->id)->where('course_id', $course->id)->count())->toBe(3);
});

test('the active-order constraint is scoped per course', function () {
    $user = User::factory()->create();
    $courseA = Course::factory()->create();
    $courseB = Course::factory()->create();

    Order::factory()->for($user, 'user')->for($courseA)->create(['status' => 'pending']);
    Order::factory()->for($user, 'user')->for($courseB)->create(['status' => 'pending']);

    expect(Order::where('user_id', $user->id)->count())->toBe(2);
});
