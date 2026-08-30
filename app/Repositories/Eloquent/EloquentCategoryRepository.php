<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Course-category management for the admin dashboard. Inherits the base
 * CRUD/listing from BaseRepository and adds a slug lookup plus slug-aware
 * create/update — the slug is normalised from the given value, or derived from
 * the name when none is given. Slug uniqueness/format is validated upstream in
 * the form request, with the `slug` unique index as the final backstop.
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

    public function createCategory(string $name, ?string $slug = null): Category
    {
        return DB::transaction(fn (): Category => Category::query()->create([
            'name' => $name,
            'slug' => Str::slug($slug ?? $name),
        ]));
    }

    public function updateCategory(Category $category, ?string $name = null, ?string $slug = null): Category
    {
        return DB::transaction(function () use ($category, $name, $slug): Category {
            $newName = $name ?? $category->name;
            $nameChanged = $name !== null && $name !== $category->name;

            $attributes = ['name' => $newName];

            if ($slug !== null || $nameChanged) {
                $attributes['slug'] = Str::slug($slug ?? $newName);
            }

            $category->update($attributes);

            return $category->refresh();
        });
    }
}
