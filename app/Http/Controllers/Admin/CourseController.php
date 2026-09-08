<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\SpatieQuery;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedInclude;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseRepositoryInterface $courses,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/courses/index');
    }

    public function fetch(): JsonResponse
    {
        $courses = $this->courses->all(
            new SpatieQuery(
                includes: [
                    AllowedInclude::avg('ratingAvg', 'ratings', 'stars'),
                    AllowedInclude::count('moduleCount', 'modules'),
                    AllowedInclude::count('lessonCount', 'lessons'),
                ],
            )
        );

        return response()->json($courses);
    }

    public function categories(): JsonResponse
    {
        return response()->json($this->categories->options());
    }
}
