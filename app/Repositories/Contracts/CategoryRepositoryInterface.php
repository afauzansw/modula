<?php

namespace App\Repositories\Contracts;

use App\Models\Category;

/**
 * Course-category management for the admin dashboard. Inherits the base
 * listing/CRUD — `all()` supports
 * `?filter[name]=…&sort=-created_at&include=courses` (plus the derived
 * `coursesCount` / `coursesExists`) — and adds slug-aware writes and a slug
 * lookup. Every slug is derived from the category name and kept unique.
 */
interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Category;

    /**
     * Create a category. The slug is derived from $name (numeric suffix on
     * collision) unless $slug is passed, in which case it is normalised and
     * must be free.
     *
     * @throws \InvalidArgumentException when an explicit $slug is empty or already taken
     */
    public function createCategory(string $name, ?string $slug = null): Category;

    /**
     * Rename a category and/or reslug it. A null $name leaves the name as-is.
     * With no explicit $slug, changing the name re-derives a fresh unique slug;
     * an unchanged name leaves the slug untouched.
     *
     * @throws \InvalidArgumentException when an explicit $slug is empty or taken by another category
     */
    public function updateCategory(Category $category, ?string $name = null, ?string $slug = null): Category;
}
