<?php

namespace App\Repositories\Contracts;

use App\Models\Category;

/**
 * Course-category management for the admin dashboard. Inherits the base
 * listing/CRUD — `all()` supports
 * `?filter[name]=…&sort=-created_at&include=courses` (plus the derived
 * `coursesCount` / `coursesExists`) — and adds slug-aware writes and a slug
 * lookup. Slug uniqueness/format is the caller's (form request's) job.
 */
interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Category;

    /**
     * Create a category. The slug is normalised from $slug, or derived from
     * $name when $slug is omitted.
     */
    public function createCategory(string $name, ?string $slug = null): Category;

    /**
     * Rename a category and/or reslug it. A null $name leaves the name as-is.
     * With no explicit $slug, changing the name re-derives the slug from it;
     * an unchanged name leaves the slug untouched.
     */
    public function updateCategory(Category $category, ?string $name = null, ?string $slug = null): Category;
}
