<?php

use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Eloquent\EloquentPaymentRepository;
use Illuminate\Http\Request;

function paymentRepo(): PaymentRepositoryInterface
{
    return app(PaymentRepositoryInterface::class);
}

test('the payment repository interface resolves to the Eloquent implementation', function () {
    expect(paymentRepo())->toBeInstanceOf(EloquentPaymentRepository::class);
});

test('all() eager-loads the order with its student and course on every row', function () {
    createPayment();
    createPayment();

    $payment = paymentRepo()->all()->items()[0];

    expect($payment->relationLoaded('order'))->toBeTrue()
        ->and($payment->order->relationLoaded('user'))->toBeTrue()
        ->and($payment->order->relationLoaded('course'))->toBeTrue();
});

test('all() filters by student name through the order relation', function () {
    $jane = createUser(['name' => 'Jane Doe']);
    createPayment(['order_id' => createOrder(['user_id' => $jane->id, 'status' => 'paid'])->id]);
    createPayment();

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['student' => 'jane']]));

    $page = paymentRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->order->user->name)->toBe('Jane Doe');
});

test('all() sorts by amount', function () {
    createPayment(['amount' => 500_000]);
    createPayment(['amount' => 100_000]);

    $this->app->instance('request', Request::create('/', 'GET', ['sort' => 'amount']));

    expect(paymentRepo()->all()->items()[0]->amount)->toBe(100_000);
});
