<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('/admin redirects to /admin/dashboard', function () {
    $this->get('/admin')->assertRedirect('/admin/dashboard');
});

test('a user with admin.dashboard can view the admin dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(AdminPermission::Dashboard->value);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/dashboard'));
});

test('a user without admin.dashboard is forbidden from the admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

test('/instructor redirects to /instructor/dashboard', function () {
    $this->get('/instructor')->assertRedirect('/instructor/dashboard');
});

test('an instructor can view the instructor dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('instructor');

    $this->actingAs($user)
        ->get(route('instructor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->component('instructor/dashboard'));
});

test('a non-instructor is forbidden from the instructor dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)->get(route('instructor.dashboard'))->assertForbidden();
});
