<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

dataset('instructor mockup pages', [
    'courses' => ['instructor.courses.index', 'instructor/courses'],
    'orders' => ['instructor.orders.index', 'instructor/orders'],
]);

test('guests are redirected to login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('instructor mockup pages');

test('a non-instructor is forbidden', function (string $routeName) {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)->get(route($routeName))->assertForbidden();
})->with('instructor mockup pages');

test('an instructor sees the mockup page', function (string $routeName, string $component) {
    $user = User::factory()->create();
    $user->assignRole('instructor');

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with('instructor mockup pages');
