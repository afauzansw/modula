<?php

use App\Models\Course;
use App\Repositories\Contracts\InstructorCourseRepositoryInterface;
use App\Repositories\Eloquent\EloquentInstructorCourseRepository;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function instructorCourseRepo(): InstructorCourseRepositoryInterface
{
    return app(InstructorCourseRepositoryInterface::class);
}

test('the interface resolves to the Eloquent implementation', function () {
    expect(instructorCourseRepo())->toBeInstanceOf(EloquentInstructorCourseRepository::class);
});

test('all() lists only the signed-in instructor courses', function () {
    $me = createUserWithRole('instructor');
    $other = createUserWithRole('instructor');

    createCourse(['instructor_id' => $me->id, 'title' => 'Mine']);
    createCourse(['instructor_id' => $other->id, 'title' => 'Theirs']);

    $this->actingAs($me);

    expect(collect(instructorCourseRepo()->all()->items())->pluck('title')->all())->toBe(['Mine']);
});

test('all() filters by title, category, price and status', function () {
    $me = createUserWithRole('instructor');
    $this->actingAs($me);

    $category = createCategory();
    createCourse(['instructor_id' => $me->id, 'title' => 'Advanced React', 'category_id' => $category->id, 'is_free' => false, 'price' => 100, 'status' => 'published']);
    createCourse(['instructor_id' => $me->id, 'title' => 'Intro Vue', 'is_free' => true, 'price' => 0, 'status' => 'draft']);

    $titlesFor = function (array $params): array {
        $this->app->instance('request', Request::create('/', 'GET', ['filter' => $params]));

        return collect(instructorCourseRepo()->all()->items())->pluck('title')->all();
    };

    expect($titlesFor(['title' => 'react']))->toBe(['Advanced React'])
        ->and($titlesFor(['category_id' => $category->id]))->toBe(['Advanced React'])
        ->and($titlesFor(['is_free' => '0']))->toBe(['Advanced React'])
        ->and($titlesFor(['status' => 'draft']))->toBe(['Intro Vue']);
});

test('create() stamps the signed-in instructor', function () {
    $me = createUserWithRole('instructor');
    $this->actingAs($me);

    $course = instructorCourseRepo()->create([
        'title' => 'Fresh Course',
        'slug' => 'fresh-course',
        'category_id' => null,
        'description' => null,
        'is_free' => true,
        'price' => 0,
        'status' => 'draft',
    ]);

    expect($course->instructor_id)->toBe($me->id);
});

test('bulkUpdate() and bulkDelete() only touch the instructor own courses', function () {
    $me = createUserWithRole('instructor');
    $other = createUserWithRole('instructor');
    $this->actingAs($me);

    $mine = createCourse(['instructor_id' => $me->id, 'status' => 'draft']);
    $theirs = createCourse(['instructor_id' => $other->id, 'status' => 'draft']);

    $published = instructorCourseRepo()->bulkUpdate([$mine->id, $theirs->id], ['status' => 'published']);
    expect($published)->toBe(1)
        ->and($mine->fresh()->status)->toBe('published')
        ->and($theirs->fresh()->status)->toBe('draft');

    $deleted = instructorCourseRepo()->bulkDelete([$mine->id, $theirs->id]);
    expect($deleted)->toBe(1)
        ->and(Course::query()->whereKey($mine->id)->exists())->toBeFalse()
        ->and(Course::query()->whereKey($theirs->id)->exists())->toBeTrue();
});
