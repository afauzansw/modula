<?php

namespace App\Repositories;

/**
 * Pagination config for `BaseRepository::all()`.
 *
 * Default: 10 rows per page. `withPaginate: false` returns every matching row
 * as a single-page paginator, so the `Paginated<T>` response shape still holds.
 */
final class PaginateQuery
{
    public function __construct(
        public bool $withPaginate = true,
        public int $perPage = 10,
    ) {}
}
