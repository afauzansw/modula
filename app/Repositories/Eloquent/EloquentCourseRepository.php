<?php

namespace App\Repositories\Eloquent;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Spatie\QueryBuilder\AllowedFilter;

class EloquentCourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = ['title'];

    /** @var list<string> */
    protected array $allowedSorts = ['title', 'price', 'status', 'created_at'];

    /** @var list<string> */
    protected array $with = ['category', 'instructor', 'media'];

    /** @var list<string> */
    protected array $fileKeys = ['thumbnail', 'certificate_template'];

    public function __construct(Course $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::exact('status');
        $this->allowedFilters[] = AllowedFilter::exact('category_id');
        $this->allowedFilters[] = AllowedFilter::callback(
            'is_free',
            fn ($query, $value) => $query->where('is_free', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
        );
    }
}
