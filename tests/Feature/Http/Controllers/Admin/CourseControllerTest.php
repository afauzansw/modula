<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function courseAdmin(): User
{
    $user = createUser();
    $user->givePermissionTo(AdminPermission::Courses->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.courses.index'))->assertRedirect(route('login'));
});

test('a user without the admin.courses permission is forbidden', function () {
    $this->actingAs(createUser())
        ->get(route('admin.courses.index'))
        ->assertForbidden();
});

test('the index page renders the shell without course data', function () {
    $this->actingAs(courseAdmin())
        ->get(route('admin.courses.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/courses/index')
            ->missing('courses'),
        );
});

test('fetch returns courses with instructor and category names as json', function () {
    $instructor = createUser(['name' => 'Grace Hopper']);
    $category = createCategory(['name' => 'Engineering']);
    createCourse(['instructor_id' => $instructor->id, 'category_id' => $category->id, 'title' => 'Compilers']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('title', 'Compilers'))
        ->toMatchArray(['instructor' => 'Grace Hopper', 'category' => 'Engineering']);
});

test('fetch reports a null category for an uncategorised course', function () {
    createCourse(['title' => 'Loose', 'category_id' => null]);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('title', 'Loose')['category'])->toBeNull();
});

test('fetch filters courses by status', function () {
    createCourse(['status' => 'published', 'title' => 'Live']);
    createCourse(['title' => 'WIP']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['filter' => ['status' => 'published']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('title')->all())->toBe(['Live']);
});

test('fetch filters courses by access (is_free)', function () {
    createCourse(['is_free' => false, 'price' => 149_000, 'title' => 'Premium']);
    createCourse(['title' => 'Gratis']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['filter' => ['is_free' => '0']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('title')->all())->toBe(['Premium']);
});

test('fetch filters courses by category', function () {
    $category = createCategory();
    createCourse(['category_id' => $category->id, 'title' => 'Matched']);
    createCourse(['title' => 'Other']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['filter' => ['category_id' => $category->id]]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('title')->all())->toBe(['Matched']);
});

test('fetch sorts courses by title', function () {
    createCourse(['title' => 'Zebra']);
    createCourse(['title' => 'Aardvark']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['sort' => 'title']))
        ->assertOk()
        ->json('data');

    $titles = collect($data)->pluck('title');

    expect($titles->search('Aardvark'))->toBeLessThan($titles->search('Zebra'));
});

test('fetch requires the admin.courses permission', function () {
    $this->actingAs(createUser())
        ->getJson(route('admin.courses.fetch'))
        ->assertForbidden();
});

test('guests cannot fetch courses', function () {
    $this->getJson(route('admin.courses.fetch'))->assertUnauthorized();
});

test('categories returns every category as id/name json', function () {
    createCategory(['name' => 'Design']);
    createCategory(['name' => 'Business']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.categories'))
        ->assertOk()
        ->json();

    expect(collect($data)->pluck('name')->all())->toBe(['Business', 'Design'])
        ->and($data[0])->toHaveKeys(['id', 'name']);
});

test('categories requires the admin.courses permission', function () {
    $this->actingAs(createUser())
        ->getJson(route('admin.courses.categories'))
        ->assertForbidden();
});
