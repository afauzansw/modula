<?php

namespace App\Repositories;

/**
 * Caller-supplied constraints for a repository listing (`BaseRepository::all()`).
 * These are applied to the base query *before* Spatie Query Builder, so they
 * can't be removed or overridden by the request's `?filter[...]` / `?sort=`:
 *
 * - each `filters` entry becomes a `where` (scalar) or `whereIn` (array);
 * - each `sorts` entry becomes an `orderBy` — a leading `-` means descending —
 *   and, being applied first, takes precedence over any request sort.
 *
 * Distinct from the repository's own `$allowed*` allow-lists, which say what the
 * *request* may ask for.
 */
final class SpatieQuery
{
    /**
     * @param  array<string, mixed>  $filters  column => value, or column => [values]
     * @param  list<string>  $sorts  e.g. 'created_at' or '-created_at'
     */
    public function __construct(
        public array $filters = [],
        public array $sorts = [],
    ) {}
}
