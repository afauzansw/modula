<?php

use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login', function () {
    $this->get(route('certificates.index'))->assertRedirect(route('login'));
});

test('the page renders the signed-in student certificates', function () {
    $student = createUser();
    $course = createCourse(['title' => 'Laravel API Mastery']);
    createCertificate([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'certificate_number' => 'CERT-2026-XYZ',
    ]);

    $this->actingAs($student)
        ->get(route('certificates.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('student/certificates')
            ->has('certificates', 1)
            ->where('certificates.0.course', 'Laravel API Mastery')
            ->where('certificates.0.certificate_number', 'CERT-2026-XYZ'),
        );
});

test('the page never shows another student certificates', function () {
    $me = createUser();
    createCertificate(['user_id' => createUser()->id]);

    $this->actingAs($me)
        ->get(route('certificates.index'))
        ->assertInertia(fn (Assert $page) => $page->has('certificates', 0));
});
