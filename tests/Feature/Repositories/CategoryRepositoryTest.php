<?php

use App\Models\Category;
use App\Models\Course;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use Illuminate\Http\Request;

function categoryRepo(): CategoryRepositoryInterface
{
    return app(CategoryRepositoryInterface::class);
}

test('the category repository interface resolves to the Eloquent implementation', function () {
    expect(categoryRepo())->toBeInstanceOf(EloquentCategoryRepository::class);
});

test('createCategory() derives the slug from the name', function () {
    $category = categoryRepo()->createCategory('Web Development');

    expect($category->slug)->toBe('web-development');
    $this->assertDatabaseHas('categories', ['name' => 'Web Development', 'slug' => 'web-development']);
});

test('createCategory() normalises an explicit slug', function () {
    expect(categoryRepo()->createCategory('Anything', 'Custom Slug!')->slug)->toBe('custom-slug');
});

test('updateCategory() renames and re-derives the slug', function () {
    $category = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);

    $updated = categoryRepo()->updateCategory($category, 'New Name');

    expect($updated->name)->toBe('New Name')
        ->and($updated->slug)->toBe('new-name');
});

test('updateCategory() normalises an explicit slug', function () {
    $category = Category::factory()->create(['slug' => 'old']);

    expect(categoryRepo()->updateCategory($category, null, 'Fresh Slug!')->slug)->toBe('fresh-slug');
});

test('updateCategory() leaves the slug untouched when the name is unchanged', function () {
    $category = Category::factory()->create(['name' => 'Design', 'slug' => 'design-original']);

    expect(categoryRepo()->updateCategory($category, 'Design')->slug)->toBe('design-original');
});

test('findBySlug() returns the matching category or null', function () {
    $category = Category::factory()->create(['slug' => 'findable']);

    expect(categoryRepo()->findBySlug('findable')->is($category))->toBeTrue()
        ->and(categoryRepo()->findBySlug('missing'))->toBeNull();
});

test('all() filters by name and eager-loads the course count', function () {
    $needle = Category::factory()->create(['name' => 'Needle']);
    Course::factory()->count(2)->for($needle)->create();
    Category::factory()->count(2)->create();

    $this->app->instance('request', Request::create('/', 'GET', [
        'filter' => ['name' => 'Needle'],
        'include' => 'coursesCount',
    ]));

    $page = categoryRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->courses_count)->toBe(2);
});
