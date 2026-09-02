<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function paymentAdmin(): User
{
    $user = createUser();
    $user->givePermissionTo(AdminPermission::Payments->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.payments.index'))->assertRedirect(route('login'));
});

test('a user without the admin.payments permission is forbidden', function () {
    $this->actingAs(createUser())
        ->get(route('admin.payments.index'))
        ->assertForbidden();
});

test('the index page renders the shell without payment data', function () {
    $this->actingAs(paymentAdmin())
        ->get(route('admin.payments.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payments/index')
            ->missing('payments'),
        );
});

test('fetch flattens the student, course and order onto each payment row', function () {
    $student = createUser(['name' => 'Grace Hopper']);
    $course = createCourse(['title' => 'Compilers', 'price' => 250_000, 'is_free' => false]);
    $order = createOrder([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'status' => 'paid',
        'order_number' => 'ORD-XYZ',
    ]);
    createPayment(['order_id' => $order->id, 'amount' => 250_000, 'method' => 'credit_card']);

    $data = $this->actingAs(paymentAdmin())
        ->getJson(route('admin.payments.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('order_number', 'ORD-XYZ'))
        ->toMatchArray([
            'student' => 'Grace Hopper',
            'course' => 'Compilers',
            'amount' => 250_000,
            'method' => 'credit_card',
        ]);
});

test('fetch filters payments by student name', function () {
    $jane = createUser(['name' => 'Jane Doe']);
    createPayment(['order_id' => createOrder(['user_id' => $jane->id, 'status' => 'paid'])->id]);
    createPayment();

    $data = $this->actingAs(paymentAdmin())
        ->getJson(route('admin.payments.fetch', ['filter' => ['student' => 'jane']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('student')->all())->toBe(['Jane Doe']);
});

test('fetch sorts payments by amount', function () {
    createPayment(['amount' => 900_000]);
    createPayment(['amount' => 50_000]);

    $data = $this->actingAs(paymentAdmin())
        ->getJson(route('admin.payments.fetch', ['sort' => 'amount']))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('amount')->first())->toBe(50_000);
});

test('fetch requires the admin.payments permission', function () {
    $this->actingAs(createUser())
        ->getJson(route('admin.payments.fetch'))
        ->assertForbidden();
});

test('guests cannot fetch payments', function () {
    $this->getJson(route('admin.payments.fetch'))->assertUnauthorized();
});
