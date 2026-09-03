<?php

use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Repositories\Eloquent\EloquentCertificateRepository;
use Illuminate\Http\Request;

function certificateRepo(): CertificateRepositoryInterface
{
    return app(CertificateRepositoryInterface::class);
}

test('the certificate repository interface resolves to the Eloquent implementation', function () {
    expect(certificateRepo())->toBeInstanceOf(EloquentCertificateRepository::class);
});

test('all() eager-loads the student and course on every row', function () {
    createCertificate();
    createCertificate();

    $certificate = certificateRepo()->all()->items()[0];

    expect($certificate->relationLoaded('user'))->toBeTrue()
        ->and($certificate->relationLoaded('course'))->toBeTrue();
});

test('forStudent() returns only that student certificates, newest issued first', function () {
    $student = createUser();

    $older = createCertificate(['user_id' => $student->id, 'issued_at' => now()->subYear()]);
    $newer = createCertificate(['user_id' => $student->id, 'issued_at' => now()]);
    createCertificate();

    $result = certificateRepo()->forStudent($student->id);

    expect($result->pluck('id')->all())->toBe([$newer->id, $older->id])
        ->and($result->first()->relationLoaded('course'))->toBeTrue();
});

test('all() filters by a partial certificate number', function () {
    createCertificate(['certificate_number' => 'CERT-ALPHA-123']);
    createCertificate(['certificate_number' => 'CERT-BETA-456']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['certificate_number' => 'alpha']]));

    $page = certificateRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->certificate_number)->toBe('CERT-ALPHA-123');
});

test('all() filters by user_id', function () {
    $grace = createUser(['name' => 'Grace Hopper']);
    createCertificate(['user_id' => $grace->id, 'certificate_number' => 'CERT-MATCH']);
    createCertificate(['certificate_number' => 'CERT-OTHER']);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['user_id' => $grace->id]]));

    $page = certificateRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->certificate_number)->toBe('CERT-MATCH');
});

test('all() filters by course_id', function () {
    $course = createCourse();
    createCertificate(['course_id' => $course->id]);
    createCertificate();

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['course_id' => $course->id]]));

    $page = certificateRepo()->all();

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->course_id)->toBe($course->id);
});

test('all() sorts by issued date', function () {
    createCertificate(['certificate_number' => 'CERT-OLD', 'issued_at' => now()->subYear()]);
    createCertificate(['certificate_number' => 'CERT-NEW', 'issued_at' => now()]);

    $this->app->instance('request', Request::create('/', 'GET', ['sort' => '-issued_at']));

    expect(certificateRepo()->all()->items()[0]->certificate_number)->toBe('CERT-NEW');
});
