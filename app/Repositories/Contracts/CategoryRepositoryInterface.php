<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Course-category management for the admin dashboard. Inherits the base
 * listing/CRUD and adds a slug lookup for route-model binding. Slug derivation
 * and uniqueness are the caller's job (form request), with the `slug` unique
 * index as the final backstop.
 */
interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Category;

    /**
     * Every category as `{id, name}`, ordered by name — for select inputs.
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function options(): Collection;
}
