<?php

use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Eloquent\EloquentCourseRepository;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function courseRepo(): CourseRepositoryInterface
{
    return app(CourseRepositoryInterface::class);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function courseAttributes(array $overrides = []): array
{
    return [
        'instructor_id' => createUser()->id,
        'title' => 'Course',
        'slug' => 'course-'.fake()->unique()->numerify('####'),
        'price' => 0,
        'is_free' => true,
        'status' => 'draft',
        ...$overrides,
    ];
}

test('the course repository interface resolves to the Eloquent implementation', function () {
    expect(courseRepo())->toBeInstanceOf(EloquentCourseRepository::class);
});

test('all() eager-loads category and instructor on every row', function () {
    foreach (range(1, 2) as $i) {
        createCourse();
    }

    $course = courseRepo()->all()->items()[0];

    expect($course->relationLoaded('category'))->toBeTrue()
        ->and($course->relationLoaded('instructor'))->toBeTrue();
});

test('all() filters by title', function () {
    createCourse(['title' => 'Advanced React Patterns']);
    createCourse(['title' => 'Intro to Vue']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['title' => 'react']]));

    $page = courseRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->title)->toBe('Advanced React Patterns');
});

test('all() filters by status', function () {
    createCourse(['status' => 'published']);
    foreach (range(1, 2) as $i) {
        createCourse();
    } // draft

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['status' => 'published']]));

    expect(courseRepo()->all()->total())->toBe(1);
});

test('all() filters by is_free with a real bool cast', function () {
    createCourse(['is_free' => false, 'price' => 149_000]);
    foreach (range(1, 2) as $i) {
        createCourse();
    } // free

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['is_free' => '0']]));

    expect(courseRepo()->all()->total())->toBe(1);
});

test('all() filters by category_id', function () {
    $category = createCategory();
    createCourse(['category_id' => $category->id]);
    foreach (range(1, 2) as $i) {
        createCourse();
    }

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['category_id' => $category->id]]));

    expect(courseRepo()->all()->total())->toBe(1);
});

test('all() sorts by title', function () {
    createCourse(['title' => 'Zeta Course']);
    createCourse(['title' => 'Alpha Course']);

    $this->app->instance('request', Request::create('/', 'GET', ['sort' => 'title']));

    expect(courseRepo()->all()->items()[0]->title)->toBe('Alpha Course');
});

test('create() moves an uploaded thumbnail into the media collection', function () {
    Storage::fake('public');

    $this->app->instance('request', Request::create('/', 'POST', [], [], [
        'thumbnail' => UploadedFile::fake()->create('cover.jpg', 20, 'image/jpeg'),
    ]));

    $course = courseRepo()->create(courseAttributes());

    expect($course->fresh()->getFirstMedia('thumbnail'))->not->toBeNull()
        ->and($course->fresh()->getFirstMedia('thumbnail')->file_name)->toBe('cover.jpg');
});

test('create() attaches nothing when the request carries no file', function () {
    Storage::fake('public');

    $course = courseRepo()->create(courseAttributes());

    expect($course->fresh()->getMedia('thumbnail'))->toBeEmpty();
});

test('update() replaces the single-file thumbnail with a newly uploaded one', function () {
    Storage::fake('public');
    $course = createCourse();
    $course->addMedia(UploadedFile::fake()->create('old.jpg', 20, 'image/jpeg'))->toMediaCollection('thumbnail');

    $this->app->instance('request', Request::create('/', 'POST', [], [], [
        'thumbnail' => UploadedFile::fake()->create('new.jpg', 20, 'image/jpeg'),
    ]));

    courseRepo()->update($course, ['title' => 'Renamed']);

    expect($course->fresh()->getMedia('thumbnail'))->toHaveCount(1)
        ->and($course->fresh()->getFirstMedia('thumbnail')->file_name)->toBe('new.jpg');
});
