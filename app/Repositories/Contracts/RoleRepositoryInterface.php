<?php

namespace App\Repositories\Contracts;

/**
 * Role listing + management for the admin dashboard.
 *
 * Adds no methods of its own: reads and deletes use the inherited CRUD/listing,
 * and `create()` / `update()` additionally sync the role's permissions from a
 * `permissions` key (a list of permission names) in the data array. The
 * write-side rules — reserved names, the permission catalogue, system-role
 * protection — are enforced by the Store/Update/BulkDestroy role form requests.
 */
interface RoleRepositoryInterface extends BaseRepositoryInterface {}
