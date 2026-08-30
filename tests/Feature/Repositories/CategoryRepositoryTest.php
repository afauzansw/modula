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

test('createCategory() appends a numeric suffix when the derived slug is taken', function () {
    Category::factory()->create(['slug' => 'design']);

    expect(categoryRepo()->createCategory('Design')->slug)->toBe('design-2');
});

test('createCategory() normalises and keeps an explicit slug', function () {
    expect(categoryRepo()->createCategory('Anything', 'Custom Slug!')->slug)->toBe('custom-slug');
});

test('createCategory() rejects an explicit slug that is already taken and creates nothing', function () {
    Category::factory()->create(['slug' => 'taken']);

    expect(fn () => categoryRepo()->createCategory('Fresh', 'taken'))->toThrow(InvalidArgumentException::class);
    expect(Category::query()->where('name', 'Fresh')->exists())->toBeFalse();
});

test('updateCategory() renames and re-derives the slug', function () {
    $category = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);

    $updated = categoryRepo()->updateCategory($category, 'New Name');

    expect($updated->name)->toBe('New Name')
        ->and($updated->slug)->toBe('new-name');
});

test('updateCategory() suffixes the re-derived slug when another category holds it', function () {
    Category::factory()->create(['slug' => 'design']);
    $category = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);

    expect(categoryRepo()->updateCategory($category, 'Design')->slug)->toBe('design-2');
});

test('updateCategory() leaves the slug untouched when the name is unchanged', function () {
    $category = Category::factory()->create(['name' => 'Design', 'slug' => 'design-original']);

    expect(categoryRepo()->updateCategory($category, 'Design')->slug)->toBe('design-original');
});

test('updateCategory() keeps an explicit slug that matches the category\'s own current slug', function () {
    $category = Category::factory()->create(['slug' => 'keep-me']);

    expect(categoryRepo()->updateCategory($category, null, 'keep-me')->slug)->toBe('keep-me');
});

test('updateCategory() rejects an explicit slug taken by another category and changes nothing', function () {
    Category::factory()->create(['slug' => 'other']);
    $category = Category::factory()->create(['name' => 'Mine', 'slug' => 'mine']);

    expect(fn () => categoryRepo()->updateCategory($category, 'Renamed', 'other'))->toThrow(InvalidArgumentException::class);
    expect($category->fresh())
        ->name->toBe('Mine')
        ->slug->toBe('mine');
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
