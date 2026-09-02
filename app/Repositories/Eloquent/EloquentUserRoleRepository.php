<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Shared listing for an admin "user directory" — a `User` model that is already
 * scoped to one role by a global scope (see the `Student` / `Instructor`
 * models). This class only adds the name/email search; every `all()` /
 * `bulkUpdate()` inherits the role constraint through the model.
 */
abstract class EloquentUserRoleRepository extends BaseRepository
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = ['name', 'email'];

    /** @var list<string> */
    protected array $allowedSorts = ['name', 'email', 'created_at'];

    public function __construct(User $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::callback('search', function ($query, $value): void {
            $term = '%'.mb_strtolower((string) $value).'%';

            $query->where(function ($query) use ($term): void {
                $query->whereRaw('lower(name) like ?', [$term])
                    ->orWhereRaw('lower(email) like ?', [$term]);
            });
        });

    }
}
