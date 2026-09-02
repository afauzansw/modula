<?php

namespace App\Repositories\Contracts;

/**
 * The admin student directory — the base listing/CRUD, scoped to the `student`
 * role by the `Student` model. Bulk block / unblock goes through the inherited
 * `bulkUpdate()`.
 */
interface StudentRepositoryInterface extends BaseRepositoryInterface {}
