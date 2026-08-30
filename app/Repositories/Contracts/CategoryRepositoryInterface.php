<?php

namespace App\Repositories\Contracts;

use App\Models\Category;

/**
 * Course-category management for the admin dashboard. Inherits the base
 * listing/CRUD — `all()` supports
 * `?filter[name]=…&sort=-created_at&include=courses` (plus the derived
 * `coursesCount` / `coursesExists`) — and adds a slug lookup for route-model
 * binding and "show category" pages. Slug derivation and uniqueness are the
 * caller's job (form request / model).
 */
interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Category;
}
