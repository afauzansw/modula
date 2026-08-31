<?php

use App\Enums\AdminPermission;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function courseAdmin(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(AdminPermission::Courses->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.courses.index'))->assertRedirect(route('login'));
});

test('a user without the admin.courses permission is forbidden', function () {
    $this->actingAs(User::factory()->create())
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
    $instructor = User::factory()->create(['name' => 'Grace Hopper']);
    $category = Category::factory()->create(['name' => 'Engineering']);
    Course::factory()->for($instructor, 'instructor')->for($category)->create(['title' => 'Compilers']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('title', 'Compilers'))
        ->toMatchArray(['instructor' => 'Grace Hopper', 'category' => 'Engineering']);
});

test('fetch reports a null category for an uncategorised course', function () {
    Course::factory()->create(['title' => 'Loose', 'category_id' => null]);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('title', 'Loose')['category'])->toBeNull();
});

test('fetch filters courses by status', function () {
    Course::factory()->published()->create(['title' => 'Live']);
    Course::factory()->create(['title' => 'WIP']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['filter' => ['status' => 'published']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('title')->all())->toBe(['Live']);
});

test('fetch filters courses by access (is_free)', function () {
    Course::factory()->paid()->create(['title' => 'Premium']);
    Course::factory()->create(['title' => 'Gratis']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['filter' => ['is_free' => '0']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('title')->all())->toBe(['Premium']);
});

test('fetch filters courses by category', function () {
    $category = Category::factory()->create();
    Course::factory()->for($category)->create(['title' => 'Matched']);
    Course::factory()->create(['title' => 'Other']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['filter' => ['category_id' => $category->id]]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('title')->all())->toBe(['Matched']);
});

test('fetch sorts courses by title', function () {
    Course::factory()->create(['title' => 'Zebra']);
    Course::factory()->create(['title' => 'Aardvark']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.fetch', ['sort' => 'title']))
        ->assertOk()
        ->json('data');

    $titles = collect($data)->pluck('title');

    expect($titles->search('Aardvark'))->toBeLessThan($titles->search('Zebra'));
});

test('fetch requires the admin.courses permission', function () {
    $this->actingAs(User::factory()->create())
        ->getJson(route('admin.courses.fetch'))
        ->assertForbidden();
});

test('guests cannot fetch courses', function () {
    $this->getJson(route('admin.courses.fetch'))->assertUnauthorized();
});

test('categories returns every category as id/name json', function () {
    Category::factory()->create(['name' => 'Design']);
    Category::factory()->create(['name' => 'Business']);

    $data = $this->actingAs(courseAdmin())
        ->getJson(route('admin.courses.categories'))
        ->assertOk()
        ->json();

    expect(collect($data)->pluck('name')->all())->toBe(['Business', 'Design'])
        ->and($data[0])->toHaveKeys(['id', 'name']);
});

test('categories requires the admin.courses permission', function () {
    $this->actingAs(User::factory()->create())
        ->getJson(route('admin.courses.categories'))
        ->assertForbidden();
});
