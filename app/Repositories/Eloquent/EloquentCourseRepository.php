<?php

namespace App\Repositories\Eloquent;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Course catalog listing for the admin dashboard. Inherits the base CRUD/listing
 * and declares the filters/sorts the courses table exposes. Every row is loaded
 * with its `category` and `instructor` so the listing can show their names
 * without an N+1.
 */
class EloquentCourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = ['title'];

    /** @var list<string> */
    protected array $allowedSorts = ['title', 'price', 'status', 'created_at'];

    /** @var list<string> */
    protected array $allowedIncludes = ['category', 'instructor', 'modules'];

    /** @var list<string> */
    protected array $with = ['category', 'instructor'];

    public function __construct(Course $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::exact('status');
        $this->allowedFilters[] = AllowedFilter::exact('category_id');

        // Cast the query-string value to a real bool so it compares cleanly
        // against the boolean column on any driver (Postgres rejects '1'/'0').
        $this->allowedFilters[] = AllowedFilter::callback(
            'is_free',
            fn ($query, $value) => $query->where('is_free', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
        );
    }
}
