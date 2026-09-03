<?php

use App\Enums\AdminPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

function certificateAdmin(): User
{
    $user = createUser();
    $user->givePermissionTo(AdminPermission::Certificates->value);

    return $user;
}

test('guests are redirected to login', function () {
    $this->get(route('admin.certificates.index'))->assertRedirect(route('login'));
});

test('a user without the admin.certificates permission is forbidden', function () {
    $this->actingAs(createUser())
        ->get(route('admin.certificates.index'))
        ->assertForbidden();
});

test('the index page renders the shell without certificate data', function () {
    $this->actingAs(certificateAdmin())
        ->get(route('admin.certificates.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/certificates/index')
            ->missing('certificates'),
        );
});

test('fetch flattens the student and course onto each certificate row', function () {
    $student = createUser(['name' => 'Grace Hopper']);
    $course = createCourse(['title' => 'Compilers']);
    createCertificate([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'certificate_number' => 'CERT-XYZ',
    ]);

    $data = $this->actingAs(certificateAdmin())
        ->getJson(route('admin.certificates.fetch'))
        ->assertOk()
        ->json('data');

    expect(collect($data)->firstWhere('certificate_number', 'CERT-XYZ'))
        ->toMatchArray(['student' => 'Grace Hopper', 'course' => 'Compilers']);
});

test('fetch filters by a partial certificate number', function () {
    createCertificate(['certificate_number' => 'CERT-ALPHA-123']);
    createCertificate(['certificate_number' => 'CERT-BETA-456']);

    $data = $this->actingAs(certificateAdmin())
        ->getJson(route('admin.certificates.fetch', ['filter' => ['certificate_number' => 'beta']]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('certificate_number')->all())->toBe(['CERT-BETA-456']);
});

test('fetch filters by student', function () {
    $grace = createUser(['name' => 'Grace Hopper']);
    createCertificate(['user_id' => $grace->id, 'certificate_number' => 'CERT-MATCH']);
    createCertificate(['certificate_number' => 'CERT-OTHER']);

    $data = $this->actingAs(certificateAdmin())
        ->getJson(route('admin.certificates.fetch', ['filter' => ['user_id' => $grace->id]]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('certificate_number')->all())->toBe(['CERT-MATCH']);
});

test('fetch filters by course', function () {
    $course = createCourse();
    createCertificate(['course_id' => $course->id, 'certificate_number' => 'CERT-MATCH']);
    createCertificate(['certificate_number' => 'CERT-OTHER']);

    $data = $this->actingAs(certificateAdmin())
        ->getJson(route('admin.certificates.fetch', ['filter' => ['course_id' => $course->id]]))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('certificate_number')->all())->toBe(['CERT-MATCH']);
});

test('fetch sorts by issued date', function () {
    createCertificate(['certificate_number' => 'CERT-OLD', 'issued_at' => now()->subYear()]);
    createCertificate(['certificate_number' => 'CERT-NEW', 'issued_at' => now()]);

    $data = $this->actingAs(certificateAdmin())
        ->getJson(route('admin.certificates.fetch', ['sort' => '-issued_at']))
        ->assertOk()
        ->json('data');

    expect(collect($data)->pluck('certificate_number')->first())->toBe('CERT-NEW');
});

test('fetch requires the admin.certificates permission', function () {
    $this->actingAs(createUser())
        ->getJson(route('admin.certificates.fetch'))
        ->assertForbidden();
});

test('guests cannot fetch certificates', function () {
    $this->getJson(route('admin.certificates.fetch'))->assertUnauthorized();
});
