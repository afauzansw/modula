<?php

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
    $category = createCategory(['slug' => 'findable']);

    expect(categoryRepo()->findBySlug('findable')->is($category))->toBeTrue()
        ->and(categoryRepo()->findBySlug('missing'))->toBeNull();
});

test('all() filters by name and eager-loads the course count', function () {
    $needle = createCategory(['name' => 'Needle']);
    foreach (range(1, 2) as $i) {
        createCourse(['category_id' => $needle->id]);
    }
    foreach (range(1, 2) as $i) {
        createCategory();
    }

    $this->app->instance('request', Request::create('/', 'GET', [
        'filter' => ['name' => 'Needle'],
        'include' => 'coursesCount',
    ]));

    $page = categoryRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->courses_count)->toBe(2);
});

test('options() returns every category as {id, name} ordered by name', function () {
    createCategory(['name' => 'Zeta']);
    createCategory(['name' => 'Alpha']);

    $options = categoryRepo()->options();

    expect($options->pluck('name')->all())->toBe(['Alpha', 'Zeta'])
        ->and($options->first())->toHaveKeys(['id', 'name']);
});
