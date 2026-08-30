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
