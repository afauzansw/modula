<?php

namespace App\Repositories\Contracts;

/**
 * Admin-account management. Inherits the base listing/CRUD, scoped to users
 * holding an admin-panel permission by the `Admin` model. `create()` /
 * `update()` also sync the account's direct permissions from a `permissions`
 * key (a list of permission names) in the data array.
 */
interface AdminRepositoryInterface extends BaseRepositoryInterface {}
