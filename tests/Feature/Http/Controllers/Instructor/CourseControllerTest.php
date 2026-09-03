<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function instructor(): User
{
    return createUserWithRole('instructor');
}

test('a non-instructor is forbidden', function () {
    $this->actingAs(createUserWithRole('student'))
        ->get(route('instructor.courses.index'))
        ->assertForbidden();
});

test('the index page renders the shell', function () {
    $this->actingAs(instructor())
        ->get(route('instructor.courses.index'))
        ->assertInertia(fn (Assert $page) => $page->component('instructor/courses/index'));
});

test('fetch returns only the signed-in instructor courses', function () {
    $me = instructor();
    createCourse(['instructor_id' => $me->id, 'title' => 'Mine']);
    createCourse(['title' => 'Someone Else']);

    $data = $this->actingAs($me)
        ->getJson(route('instructor.courses.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('title')->all())->toBe(['Mine']);
});

test('fetch filters by category, price and status', function () {
    $me = instructor();
    $category = createCategory();
    createCourse(['instructor_id' => $me->id, 'title' => 'Paid Live', 'category_id' => $category->id, 'is_free' => false, 'price' => 500, 'status' => 'published']);
    createCourse(['instructor_id' => $me->id, 'title' => 'Free Draft', 'is_free' => true, 'status' => 'draft']);

    $titles = fn (array $filter) => collect(
        $this->actingAs($me)->getJson(route('instructor.courses.fetch', ['filter' => $filter]))->assertOk()->json('data')
    )->pluck('title')->all();

    expect($titles(['category_id' => $category->id]))->toBe(['Paid Live'])
        ->and($titles(['is_free' => '0']))->toBe(['Paid Live'])
        ->and($titles(['status' => 'draft']))->toBe(['Free Draft']);
});

test('the create page passes the category list', function () {
    createCategory(['name' => 'Design']);

    $this->actingAs(instructor())
        ->get(route('instructor.courses.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('instructor/courses/create')
            ->has('categories'),
        );
});

test('store creates a draft course owned by the instructor, deriving the slug', function () {
    $me = instructor();

    $this->actingAs($me)
        ->post(route('instructor.courses.store'), [
            'title' => 'Modern React',
            'is_free' => true,
            'status' => 'draft',
        ])
        ->assertRedirect(route('instructor.courses.index'));

    $course = Course::query()->where('title', 'Modern React')->firstOrFail();

    expect($course->instructor_id)->toBe($me->id)
        ->and($course->slug)->toBe('modern-react')
        ->and($course->is_free)->toBeTrue()
        ->and($course->price)->toBe(0);
});

test('store stores an uploaded thumbnail', function () {
    Storage::fake('public');

    $this->actingAs(instructor())
        ->post(route('instructor.courses.store'), [
            'title' => 'With Cover',
            'is_free' => true,
            'status' => 'draft',
            'thumbnail' => UploadedFile::fake()->create('c.jpg', 20, 'image/jpeg'),
        ])
        ->assertRedirect();

    expect(Course::query()->where('title', 'With Cover')->firstOrFail()->getFirstMedia('thumbnail'))
        ->not->toBeNull();
});

test('store rejects a title whose slug already exists', function () {
    createCourse(['title' => 'Taken', 'slug' => 'taken']);

    $this->actingAs(instructor())
        ->post(route('instructor.courses.store'), ['title' => 'taken', 'is_free' => true, 'status' => 'draft'])
        ->assertInvalid(['title']);
});

test('store requires a positive price for a paid course', function () {
    $this->actingAs(instructor())
        ->post(route('instructor.courses.store'), ['title' => 'Paid', 'is_free' => false, 'price' => 149000, 'status' => 'published'])
        ->assertRedirect();

    expect(Course::query()->where('title', 'Paid')->value('price'))->toBe(149000);
});

test('edit and update work on your own course only', function () {
    $me = instructor();
    $mine = createCourse(['instructor_id' => $me->id, 'title' => 'Old']);
    $theirs = createCourse(['title' => 'Not Yours']);

    $this->actingAs($me)->get(route('instructor.courses.edit', $mine))->assertOk();
    $this->actingAs($me)->get(route('instructor.courses.edit', $theirs))->assertNotFound();

    $this->actingAs($me)
        ->put(route('instructor.courses.update', $mine), [
            'title' => 'Renamed',
            'is_free' => false,
            'price' => 99000,
            'status' => 'published',
        ])
        ->assertRedirect(route('instructor.courses.index'));

    expect($mine->fresh())
        ->title->toBe('Renamed')
        ->status->toBe('published')
        ->price->toBe(99000);
});

test('destroy deletes your own course, 404s on another instructor course', function () {
    $me = instructor();
    $mine = createCourse(['instructor_id' => $me->id]);
    $theirs = createCourse();

    $this->actingAs($me)->delete(route('instructor.courses.destroy', $theirs))->assertNotFound();
    $this->actingAs($me)->delete(route('instructor.courses.destroy', $mine))->assertRedirect();

    expect(Course::query()->whereKey($mine->id)->exists())->toBeFalse()
        ->and(Course::query()->whereKey($theirs->id)->exists())->toBeTrue();
});

test('bulk update status publishes / unpublishes only your own courses', function () {
    $me = instructor();
    $mine = createCourse(['instructor_id' => $me->id, 'status' => 'draft']);
    $theirs = createCourse(['status' => 'draft']);

    $this->actingAs($me)
        ->patch(route('instructor.courses.bulk-update-status'), [
            'ids' => [$mine->id, $theirs->id],
            'status' => 'published',
        ])
        ->assertRedirect(route('instructor.courses.index'));

    expect($mine->fresh()->status)->toBe('published')
        ->and($theirs->fresh()->status)->toBe('draft');
});

test('bulk update status rejects a status other than draft or published', function () {
    $me = instructor();
    $course = createCourse(['instructor_id' => $me->id]);

    $this->actingAs($me)
        ->patch(route('instructor.courses.bulk-update-status'), ['ids' => [$course->id], 'status' => 'archived'])
        ->assertInvalid(['status']);
});

test('bulk destroy deletes only your own selected courses', function () {
    $me = instructor();
    $mine = createCourse(['instructor_id' => $me->id]);
    $theirs = createCourse();

    $this->actingAs($me)
        ->delete(route('instructor.courses.bulk-destroy'), ['ids' => [$mine->id, $theirs->id]])
        ->assertRedirect(route('instructor.courses.index'));

    expect(Course::query()->whereKey($mine->id)->exists())->toBeFalse()
        ->and(Course::query()->whereKey($theirs->id)->exists())->toBeTrue();
});

test('bulk destroy rejects an empty selection', function () {
    $this->actingAs(instructor())
        ->delete(route('instructor.courses.bulk-destroy'), ['ids' => []])
        ->assertInvalid(['ids']);
});
