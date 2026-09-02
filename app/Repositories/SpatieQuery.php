<?php

namespace App\Repositories;

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * Per-call override of a repository's allow-lists for `BaseRepository::all()`.
 *
 * When `filters` / `sorts` is non-empty it replaces the repository's own
 * `$allowedFilters` / `$allowedSorts` for that one call — i.e. it says what the
 * request's `?filter[...]` / `?sort=` may reference. Empty (the default) keeps
 * the repository's declared allow-lists.
 */
final class SpatieQuery
{
    /**
     * @param  list<string|AllowedFilter>  $filters
     * @param  list<string|AllowedSort>  $sorts
     */
    public function __construct(
        public array $filters = [],
        public array $sorts = [],
    ) {}
}
