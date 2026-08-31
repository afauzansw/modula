<?php

use App\Models\Category;
use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Eloquent\EloquentCourseRepository;
use Illuminate\Http\Request;

function courseRepo(): CourseRepositoryInterface
{
    return app(CourseRepositoryInterface::class);
}

test('the course repository interface resolves to the Eloquent implementation', function () {
    expect(courseRepo())->toBeInstanceOf(EloquentCourseRepository::class);
});

test('all() eager-loads category and instructor on every row', function () {
    Course::factory()->count(2)->create();

    $course = courseRepo()->all()->items()[0];

    expect($course->relationLoaded('category'))->toBeTrue()
        ->and($course->relationLoaded('instructor'))->toBeTrue();
});

test('all() filters by title', function () {
    Course::factory()->create(['title' => 'Advanced React Patterns']);
    Course::factory()->create(['title' => 'Intro to Vue']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['title' => 'react']]));

    $page = courseRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->title)->toBe('Advanced React Patterns');
});

test('all() filters by status', function () {
    Course::factory()->published()->create();
    Course::factory()->count(2)->create(); // draft

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['status' => 'published']]));

    expect(courseRepo()->all()->total())->toBe(1);
});

test('all() filters by is_free with a real bool cast', function () {
    Course::factory()->paid()->create();
    Course::factory()->count(2)->create(); // free

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['is_free' => '0']]));

    expect(courseRepo()->all()->total())->toBe(1);
});

test('all() filters by category_id', function () {
    $category = Category::factory()->create();
    Course::factory()->for($category)->create();
    Course::factory()->count(2)->create();

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['category_id' => $category->id]]));

    expect(courseRepo()->all()->total())->toBe(1);
});

test('all() sorts by title', function () {
    Course::factory()->create(['title' => 'Zeta Course']);
    Course::factory()->create(['title' => 'Alpha Course']);

    $this->app->instance('request', Request::create('/', 'GET', ['sort' => 'title']));

    expect(courseRepo()->all()->items()[0]->title)->toBe('Alpha Course');
});
