<?php

namespace App\Repositories\Contracts;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Collection;

/**
 * Issued-certificate reads. The admin listing inherits the base CRUD (rows
 * carry their student + course); the student area uses `forStudent()`.
 */
interface CertificateRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * The given student's certificates, most recently issued first, each with
     * its course loaded.
     *
     * @return Collection<int, Certificate>
     */
    public function forStudent(int $studentId): Collection;
}
