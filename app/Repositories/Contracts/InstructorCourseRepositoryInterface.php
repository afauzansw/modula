<?php

namespace App\Repositories\Contracts;

/**
 * Course management for the instructor area — the base CRUD/listing, scoped to
 * the signed-in instructor by the `InstructorCourse` model. `create()` stamps
 * `instructor_id`; `bulkUpdate()` / `bulkDelete()` inherit the scope so a bulk
 * publish or delete only touches that instructor's courses.
 */
interface InstructorCourseRepositoryInterface extends BaseRepositoryInterface {}
