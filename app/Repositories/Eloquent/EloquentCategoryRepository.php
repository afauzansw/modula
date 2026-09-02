<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentCategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    /** @var list<string> */
    protected array $allowedFilters = ['name', 'slug'];

    /** @var list<string> */
    protected array $allowedSorts = ['name', 'created_at'];

    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::query()->where('slug', $slug)->first();
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    public function options(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ]);
    }
}
