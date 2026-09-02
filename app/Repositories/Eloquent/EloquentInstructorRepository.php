<?php

namespace App\Repositories\Eloquent;

use App\Models\Instructor;
use App\Repositories\Contracts\InstructorRepositoryInterface;

class EloquentInstructorRepository extends EloquentUserRoleRepository implements InstructorRepositoryInterface
{
    public function __construct(Instructor $model)
    {
        parent::__construct($model);
    }
}
