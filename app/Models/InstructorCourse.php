<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

/**
 * `Course` permanently scoped to the signed-in instructor (`instructor_id`).
 * The instructor course-management screen reads and writes through this, so
 * listing, edit-binding and bulk publish/delete can only touch that
 * instructor's own courses. Media links resolve to `Course` (getMorphClass)
 * so the admin catalog sees the same thumbnails.
 */
class InstructorCourse extends Course
{
    protected $table = 'courses';

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('own', fn ($query) => $query->where('instructor_id', Auth::id()));
    }

    public function getMorphClass(): string
    {
        return Course::class;
    }
}
