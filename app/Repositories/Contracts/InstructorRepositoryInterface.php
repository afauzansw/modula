<?php

namespace App\Repositories\Contracts;

/**
 * The admin instructor directory — the base listing/CRUD, scoped to the
 * `instructor` role by the `Instructor` model. Bulk block / unblock goes
 * through the inherited `bulkUpdate()`.
 */
interface InstructorRepositoryInterface extends BaseRepositoryInterface {}
