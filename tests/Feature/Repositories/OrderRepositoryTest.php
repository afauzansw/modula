<?php

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquent\EloquentOrderRepository;

function orderRepo(): OrderRepositoryInterface
{
    return app(OrderRepositoryInterface::class);
}

test('the order repository interface resolves to the Eloquent implementation', function () {
    expect(orderRepo())->toBeInstanceOf(EloquentOrderRepository::class);
});

test('forStudent() returns only that student orders, newest first', function () {
    $student = createUser();

    $older = createOrder(['user_id' => $student->id, 'created_at' => now()->subDay()]);
    $newer = createOrder(['user_id' => $student->id, 'created_at' => now()]);
    createOrder();

    expect(orderRepo()->forStudent($student->id)->pluck('id')->all())->toBe([$newer->id, $older->id]);
});

test('forStudent() eager-loads the course and payments', function () {
    $student = createUser();
    $order = createOrder(['user_id' => $student->id, 'status' => 'paid']);
    createPayment(['order_id' => $order->id]);

    $loaded = orderRepo()->forStudent($student->id)->first();

    expect($loaded->relationLoaded('course'))->toBeTrue()
        ->and($loaded->relationLoaded('payments'))->toBeTrue()
        ->and($loaded->payments)->toHaveCount(1);
});
