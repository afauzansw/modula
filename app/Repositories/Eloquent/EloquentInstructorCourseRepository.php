<?php

namespace App\Repositories\Eloquent;

use App\Models\InstructorCourse;
use App\Repositories\Contracts\InstructorCourseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;

class EloquentInstructorCourseRepository extends BaseRepository implements InstructorCourseRepositoryInterface
{
    /** @var list<string|AllowedFilter> */
    protected array $allowedFilters = ['title'];

    /** @var list<string> */
    protected array $allowedSorts = ['title', 'price', 'status', 'created_at'];

    /** @var list<string> */
    protected array $with = ['category', 'media'];

    /** @var list<string> */
    protected array $fileKeys = ['thumbnail'];

    public function __construct(InstructorCourse $model)
    {
        parent::__construct($model);

        $this->allowedFilters[] = AllowedFilter::exact('status');
        $this->allowedFilters[] = AllowedFilter::exact('category_id');
        $this->allowedFilters[] = AllowedFilter::callback(
            'is_free',
            fn ($query, $value) => $query->where('is_free', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $data['instructor_id'] = Auth::id();

        return parent::create($data);
    }
}
