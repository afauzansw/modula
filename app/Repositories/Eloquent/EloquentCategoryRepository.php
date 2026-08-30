<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Course-category management for the admin dashboard. Inherits the base
 * CRUD/listing from BaseRepository and adds slug-aware create/update plus a
 * slug lookup — every category slug is derived from its name and kept unique,
 * with the `slug` unique index as the race-condition backstop.
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
            'slug' => $this->resolveSlug($name, $slug),
        ]));
    }

    public function updateCategory(Category $category, ?string $name = null, ?string $slug = null): Category
    {
        return DB::transaction(function () use ($category, $name, $slug): Category {
            $newName = $name ?? $category->name;
            $nameChanged = $name !== null && $name !== $category->name;

            $attributes = ['name' => $newName];

            if ($slug !== null || $nameChanged) {
                $attributes['slug'] = $this->resolveSlug($newName, $slug, $category->id);
            }

            $category->update($attributes);

            return $category->refresh();
        });
    }

    /**
     * Turn a name — or an explicit slug — into a valid, unique slug. An explicit
     * slug is normalised and must be free; a derived slug gains a numeric suffix
     * until it is. $ignoreId excludes the row being updated from the check.
     */
    private function resolveSlug(string $name, ?string $explicitSlug, ?int $ignoreId = null): string
    {
        if ($explicitSlug !== null) {
            $slug = Str::slug($explicitSlug);

            if ($slug === '') {
                throw new InvalidArgumentException("The slug '{$explicitSlug}' is empty once normalised.");
            }

            if ($this->slugExists($slug, $ignoreId)) {
                throw new InvalidArgumentException("The slug '{$slug}' is already taken.");
            }

            return $slug;
        }

        $base = Str::slug($name);

        if ($base === '') {
            throw new InvalidArgumentException("Cannot derive a slug from the name '{$name}'.");
        }

        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        return Category::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
