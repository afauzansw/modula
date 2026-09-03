<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function __construct(private readonly CertificateRepositoryInterface $certificates) {}

    public function index(Request $request): Response
    {
        $certificates = $this->certificates
            ->forStudent($request->user()->id)
            ->map(fn (Certificate $certificate): array => [
                'id' => $certificate->id,
                'course' => $certificate->course->title,
                'certificate_number' => $certificate->certificate_number,
                'issued_at' => $certificate->issued_at->toIso8601String(),
            ]);

        return Inertia::render('student/certificates', ['certificates' => $certificates]);
    }
}
