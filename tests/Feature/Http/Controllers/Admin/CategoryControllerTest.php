<?php

use App\Enums\AdminPermission;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function categoryAdmin(): User
{
    $user = createUser();
    $user->givePermissionTo(AdminPermission::Categories->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
});

test('a user without the admin.categories permission is forbidden', function () {
    $this->actingAs(createUser())
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('the index page renders the shell without category data', function () {
    $this->actingAs(categoryAdmin())
        ->get(route('admin.categories.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/categories/index')
            ->missing('categories'),
        );
});

test('create and edit have no routes (they are modals on the index)', function () {
    expect(Route::has('admin.categories.create'))->toBeFalse()
        ->and(Route::has('admin.categories.edit'))->toBeFalse();
});

test('fetch returns categories with their name and slug as json', function () {
    createCategory(['name' => 'Web Development', 'slug' => 'web-development']);

    $data = $this->actingAs(categoryAdmin())
        ->getJson(route('admin.categories.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('name', 'Web Development'))
        ->toMatchArray(['name' => 'Web Development', 'slug' => 'web-development']);
});

test('fetch filters categories by a partial name match', function () {
    createCategory(['name' => 'Data Science']);
    createCategory(['name' => 'Design']);

    $data = $this->actingAs(categoryAdmin())
        ->getJson(route('admin.categories.fetch', ['filter' => ['name' => 'scien']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('name')->all())->toBe(['Data Science']);
});

test('fetch sorts categories by name', function () {
    createCategory(['name' => 'Zeta']);
    createCategory(['name' => 'Alpha']);

    $data = $this->actingAs(categoryAdmin())
        ->getJson(route('admin.categories.fetch', ['sort' => 'name']))
        ->assertOk()
        ->json('data');

    $names = collect($data)->pluck('name');

    expect($names->search('Alpha'))->toBeLessThan($names->search('Zeta'));
});

test('fetch requires the admin.categories permission', function () {
    $this->actingAs(createUser())
        ->getJson(route('admin.categories.fetch'))
        ->assertForbidden();
});

test('guests cannot fetch categories', function () {
    $this->getJson(route('admin.categories.fetch'))->assertUnauthorized();
});

test('store creates a category and derives its slug from the name', function () {
    $this->actingAs(categoryAdmin())
        ->post(route('admin.categories.store'), ['name' => 'Mobile Development'])
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::query()->where('name', 'Mobile Development')->value('slug'))
        ->toBe('mobile-development');
});

test('store rejects a blank name', function () {
    $this->actingAs(categoryAdmin())
        ->post(route('admin.categories.store'), ['name' => ''])
        ->assertInvalid(['name']);
});

test('store rejects a name whose slug already exists', function () {
    createCategory(['name' => 'Web Development', 'slug' => 'web-development']);

    $this->actingAs(categoryAdmin())
        ->post(route('admin.categories.store'), ['name' => 'Web development'])
        ->assertInvalid(['name']);
});

test('update renames a category and its slug follows', function () {
    $category = createCategory(['name' => 'Web Dev', 'slug' => 'web-dev']);

    $this->actingAs(categoryAdmin())
        ->put(route('admin.categories.update', $category), ['name' => 'Web Engineering'])
        ->assertRedirect(route('admin.categories.index'));

    expect($category->fresh())
        ->name->toBe('Web Engineering')
        ->slug->toBe('web-engineering');
});

test('update lets a category keep its own name', function () {
    $category = createCategory(['name' => 'Design', 'slug' => 'design']);

    $this->actingAs(categoryAdmin())
        ->put(route('admin.categories.update', $category), ['name' => 'Design'])
        ->assertValid();
});

test('update rejects renaming onto another category slug', function () {
    createCategory(['name' => 'Design', 'slug' => 'design']);
    $category = createCategory(['name' => 'Data', 'slug' => 'data']);

    $this->actingAs(categoryAdmin())
        ->put(route('admin.categories.update', $category), ['name' => 'Design'])
        ->assertInvalid(['name']);
});

test('destroy deletes a category and uncategorizes its courses', function () {
    $category = createCategory();
    $course = createCourse(['category_id' => $category->id]);

    $this->actingAs(categoryAdmin())
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::query()->whereKey($category->id)->exists())->toBeFalse()
        ->and($course->fresh()->category_id)->toBeNull();
});

test('bulk destroy deletes every selected category', function () {
    $first = createCategory();
    $second = createCategory();

    $this->actingAs(categoryAdmin())
        ->delete(route('admin.categories.bulk-destroy'), ['ids' => [$first->id, $second->id]])
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::query()->whereIn('id', [$first->id, $second->id])->count())->toBe(0);
});

test('bulk destroy rejects an empty selection', function () {
    $this->actingAs(categoryAdmin())
        ->delete(route('admin.categories.bulk-destroy'), ['ids' => []])
        ->assertInvalid(['ids']);
});

test('bulk destroy requires the admin.categories permission', function () {
    $this->actingAs(createUser())
        ->delete(route('admin.categories.bulk-destroy'), ['ids' => [1]])
        ->assertForbidden();
});

test('store requires the admin.categories permission', function () {
    $this->actingAs(createUser())
        ->post(route('admin.categories.store'), ['name' => 'Blocked'])
        ->assertForbidden();
});
