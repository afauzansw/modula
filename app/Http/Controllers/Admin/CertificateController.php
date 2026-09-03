<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function __construct(private readonly CertificateRepositoryInterface $certificates) {}

    public function index(): Response
    {
        return Inertia::render('admin/certificates/index');
    }

    public function fetch(): JsonResponse
    {
        $certificates = $this->certificates->all();

        $rows = [];

        foreach ($certificates->items() as $certificate) {
            if (! $certificate instanceof Certificate) {
                continue;
            }

            $rows[] = [
                'id' => $certificate->id,
                'student' => $certificate->user->name,
                'course' => $certificate->course->title,
                'certificate_number' => $certificate->certificate_number,
                'issued_at' => $certificate->issued_at->toIso8601String(),
            ];
        }

        return $this->paginatedJson($certificates, $rows);
    }
}
