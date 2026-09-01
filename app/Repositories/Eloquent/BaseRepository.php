<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\LoadQuery;
use App\Repositories\PaginateQuery;
use App\Repositories\SpatieQuery;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
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

    protected array $fileKeys = [];

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
     * @return LengthAwarePaginator<int, Model>
     */
    public function all(
        SpatieQuery $scope = new SpatieQuery,
        LoadQuery $load = new LoadQuery,
        PaginateQuery $paginate = new PaginateQuery,
    ): LengthAwarePaginator {
        $base = $this->model->newQuery()->with([...$this->with, ...$load->with]);

        if ($load->select !== []) {
            $base->select($load->select);
        }

        $query = QueryBuilder::for($base)
            ->allowedFilters(...($scope->filters ?? $this->allowedFilters))
            ->allowedSorts(...($scope->sorts ?? $this->allowedSorts));

        if ($paginate->withPaginate) {
            return $query->paginate($paginate->perPage)->appends(request()->query());
        }

        $rows = $query->get();

        return new Paginator($rows, $rows->count(), max(1, $rows->count()), 1);
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
        return DB::transaction(function () use ($data): Model {

            $model = $this->model->newQuery()->create($data);

            foreach ($this->fileKeys as $fileKey) {
                $model->addMultipleMediaFromRequest([$fileKey])
                    ->each(function ($image) use ($fileKey) {
                        $image->toMediaCollection($fileKey);
                    });
            }

            $this->afterCreate($model, $data);

            return $model;
        });
    }

    /**
     * Hook fired inside create()'s transaction, right after the row is inserted
     * (and any $fileKeys media attached). Override in a concrete repository to
     * persist related records from the same $data payload — e.g. sync a pivot
     * or create child rows. No-op by default.
     *
     * @param  array<string, mixed>  $data
     */
    protected function afterCreate(Model $model, array $data): void {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data): Model {
            $model->update($data);

            $this->afterUpdate($model, $data);

            return $model;
        });
    }

    /**
     * Hook fired inside update()'s transaction, right after the row is saved.
     * Override in a concrete repository to persist related records from the
     * same $data payload — e.g. re-sync a pivot. No-op by default.
     *
     * @param  array<string, mixed>  $data
     */
    protected function afterUpdate(Model $model, array $data): void {}

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
