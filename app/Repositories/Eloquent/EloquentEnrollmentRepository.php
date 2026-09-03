<?php

namespace App\Repositories\Eloquent;

use App\Models\Enrollment;
use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentEnrollmentRepository extends BaseRepository implements EnrollmentRepositoryInterface
{
    public function __construct(Enrollment $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Collection<int, Enrollment>
     */
    public function forStudent(int $studentId): Collection
    {
        return Enrollment::query()
            ->where('user_id', $studentId)
            ->with(['course.instructor', 'course.media'])
            ->latest()
            ->get();
    }
}
