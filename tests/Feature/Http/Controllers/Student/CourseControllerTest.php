<?php

use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login', function () {
    $this->get(route('courses.index'))->assertRedirect(route('login'));
});

test('the page renders the signed-in student enrolled courses with progress', function () {
    $student = createUser();
    $course = createCourse(['title' => 'Modern React']);
    createEnrollment([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'progress_percent' => 40,
        'status' => 'active',
    ]);

    $this->actingAs($student)
        ->get(route('courses.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('student/courses')
            ->has('courses', 1)
            ->where('courses.0.title', 'Modern React')
            ->where('courses.0.progress_percent', 40)
            ->where('courses.0.status', 'active'),
        );
});

test('the page never shows another student courses', function () {
    $me = createUser();
    $someoneElse = createUser();
    createEnrollment(['user_id' => $someoneElse->id]);

    $this->actingAs($me)
        ->get(route('courses.index'))
        ->assertInertia(fn (Assert $page) => $page->has('courses', 0));
});
