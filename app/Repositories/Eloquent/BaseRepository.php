<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Foundation for every Eloquent repository. Owns the shared CRUD, transactional
 * writes, bulk operations, and the Spatie Query Builder-powered listing so a
 * concrete repository only declares its `$model` and its allowed filters/sorts.
 *
 * Abstract on purpose — only concrete subclasses are bound to interfaces in
 * RepositoryServiceProvider. Concrete repositories that need entity-typed return
 * values declare their own methods on top (see EloquentAuthRepository).
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    /**
     * Filters exposed to `?filter[...]=` on the listing endpoint.
     *
     * @var list<string|AllowedFilter>
     */
    protected array $allowedFilters = [];

    /**
     * Sorts exposed to `?sort=` on the listing endpoint.
     *
     * @var list<string|AllowedSort>
     */
    protected array $allowedSorts = [];

    /**
     * Relations eager-loadable via `?include=` on the listing endpoint.
     *
     * @var list<string|AllowedInclude>
     */
    protected array $allowedIncludes = [];

    /**
     * Relations eager-loaded on every `all()` call, regardless of the request —
     * for relations the listing always needs (vs `$allowedIncludes`, which is
     * opt-in per request). `?include=` still adds more on top.
     *
     * @var list<string>
     */
    protected array $with = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Model>
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $base = $this->model->newQuery()->with($this->with);

        foreach ($filters as $column => $value) {
            is_array($value)
                ? $base->whereIn($column, $value)
                : $base->where($column, $value);
        }

        return QueryBuilder::for($base)
            ->allowedFilters(...$this->allowedFilters)
            ->allowedSorts(...$this->allowedSorts)
            ->allowedIncludes(...$this->allowedIncludes)
            ->paginate($perPage)
            ->appends(request()->query());
    }

    public function find(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data): Model {
            $model->update($data);

            return $model;
        });
    }

    /**
     * Mass-update every row matching $conditions. Supported condition forms:
     * `['column' => $value]` (equality), `['column' => [$a, $b]]` (whereIn),
     * and `[fn (Builder $q) => ...]` (arbitrary) — mix freely.
     *
     * @param  array<string|int, mixed>  $conditions
     * @param  array<string, mixed>  $data
     */
    public function updateWhere(array $conditions, array $data): int
    {
        return DB::transaction(function () use ($conditions, $data): int {
            $query = $this->model->newQuery();

            foreach ($conditions as $key => $value) {
                if ($value instanceof Closure) {
                    $query->where($value);
                } elseif (is_string($key) && is_array($value)) {
                    $query->whereIn($key, $value);
                } elseif (is_string($key)) {
                    $query->where($key, $value);
                }
            }

            return $query->update($data);
        });
    }

    public function delete(Model $model): bool
    {
        return $model->delete() ?? false;
    }

    /**
     * Apply the same $data to every row whose id is in $ids — e.g. flipping a
     * batch of records to `status => 'published'`. One query, one transaction.
     *
     * @param  array<int, int>  $ids
     * @param  array<string, mixed>  $data
     */
    public function bulkUpdate(array $ids, array $data): int
    {
        return DB::transaction(fn (): int => $this->model->newQuery()->whereIn('id', $ids)->update($data));
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn (): int => $this->model->newQuery()->whereIn('id', $ids)->delete());
    }
}
