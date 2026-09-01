<?php

namespace App\Repositories;

/**
 * Eager-load / column-selection config for `BaseRepository::all()`.
 *
 * - `with` is merged on top of the repository's own `$with` (always-loaded
 *   relations), so the caller adds extra relations rather than replacing them.
 * - `select` (empty = all columns) is a forced `select` applied before Spatie
 *   Query Builder. When narrowing columns, remember to keep any key a `with`
 *   relation needs (e.g. `category_id` for a `category` relation).
 */
final class LoadQuery
{
    /**
     * @param  list<string>  $with
     * @param  list<string>  $select
     */
    public function __construct(
        public array $with = [],
        public array $select = [],
    ) {}
}
