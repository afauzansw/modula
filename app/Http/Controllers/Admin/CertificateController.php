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

        return response()->json($certificates);
    }
}
