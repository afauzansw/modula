<?php

namespace App\Repositories\Contracts;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;

/**
 * Enrollment reads for the student area. Inherits the base CRUD and adds the
 * "my courses" lookup.
 */
interface EnrollmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * The given student's enrollments, newest first, each with its course's
     * instructor and thumbnail media loaded.
     *
     * @return Collection<int, Enrollment>
     */
    public function forStudent(int $studentId): Collection;
}
