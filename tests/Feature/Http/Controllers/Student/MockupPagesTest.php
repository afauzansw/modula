<?php

use Inertia\Testing\AssertableInertia as Assert;

dataset('student mockup pages', [
    'courses' => ['courses.index', 'student/courses'],
    'payments' => ['payments.index', 'student/payments'],
    'certificates' => ['certificates.index', 'student/certificates'],
]);

test('guests are redirected to login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('student mockup pages');

test('an authenticated user sees the mockup page', function (string $routeName, string $component) {
    $user = createUser();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with('student mockup pages');
