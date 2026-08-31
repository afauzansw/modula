<?php

namespace App\Repositories\Contracts;

/**
 * Course catalog listing for the admin dashboard. A thin BaseRepository subclass
 * — it inherits the CRUD/listing and adds nothing yet; `all()` rows are always
 * eager-loaded with their `category` and `instructor` (see EloquentCourseRepository).
 */
interface CourseRepositoryInterface extends BaseRepositoryInterface {}
