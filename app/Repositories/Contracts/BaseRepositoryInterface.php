<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract shared by every Eloquent repository. Concrete repositories extend
 * BaseRepository (which implements this) and only add entity-specific methods.
 */
interface BaseRepositoryInterface
{
    /**
     * Paginated, filterable, sortable listing driven by the request query string
     * (`?filter[...]=`, `?sort=`, `?include=`) plus any forced $filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Model>
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function findOrFail(int $id): Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model;

    /**
     * Mass-update every row matching $conditions. Returns the affected row count.
     *
     * @param  array<string|int, mixed>  $conditions
     * @param  array<string, mixed>  $data
     */
    public function updateWhere(array $conditions, array $data): int;

    public function delete(Model $model): bool;

    /**
     * Apply the same $data to every row whose id is in $ids (e.g. a bulk status
     * change). Returns the affected row count.
     *
     * @param  array<int, int>  $ids
     * @param  array<string, mixed>  $data
     */
    public function bulkUpdate(array $ids, array $data): int;

    /**
     * Delete every row whose id is in $ids. Returns the deleted row count.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int;
}
