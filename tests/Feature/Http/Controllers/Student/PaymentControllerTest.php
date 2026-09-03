<?php

use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login', function () {
    $this->get(route('payments.index'))->assertRedirect(route('login'));
});

test('the page renders the signed-in student orders with the payment method', function () {
    $student = createUser();
    $course = createCourse(['title' => 'Laravel API Mastery']);
    $order = createOrder([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'amount' => 249_000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    createPayment(['order_id' => $order->id, 'method' => 'bank_transfer']);

    $this->actingAs($student)
        ->get(route('payments.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('student/payments')
            ->has('orders', 1)
            ->where('orders.0.course', 'Laravel API Mastery')
            ->where('orders.0.amount', 249_000)
            ->where('orders.0.status', 'paid')
            ->where('orders.0.method', 'bank_transfer'),
        );
});

test('a pending order shows no method', function () {
    $student = createUser();
    createOrder(['user_id' => $student->id, 'status' => 'pending']);

    $this->actingAs($student)
        ->get(route('payments.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.0.method', null)
            ->where('orders.0.status', 'pending'),
        );
});

test('the page never shows another student orders', function () {
    $me = createUser();
    createOrder(['user_id' => createUser()->id]);

    $this->actingAs($me)
        ->get(route('payments.index'))
        ->assertInertia(fn (Assert $page) => $page->has('orders', 0));
});
