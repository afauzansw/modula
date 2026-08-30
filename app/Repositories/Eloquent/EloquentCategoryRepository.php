<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

/**
 * Course-category management for the admin dashboard. A thin BaseRepository
 * subclass: it inherits the CRUD/listing and adds only the slug lookup. Slug
 * derivation and uniqueness live upstream (form request / model), with the
 * `slug` unique index as the final backstop.
 */
class EloquentCategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    /** @var list<string> */
    protected array $allowedFilters = ['name', 'slug'];

    /** @var list<string> */
    protected array $allowedSorts = ['name', 'created_at'];

    /**
     * Allowing `courses` also enables the derived `coursesCount` /
     * `coursesExists` includes — Spatie Query Builder appends them.
     *
     * @var list<string>
     */
    protected array $allowedIncludes = ['courses'];

    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::query()->where('slug', $slug)->first();
    }
}
