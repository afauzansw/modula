<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

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
        $courses = $this->courses->all();

        $rows = [];

        foreach ($courses->items() as $course) {
            if (! $course instanceof Course) {
                continue;
            }

            $rows[] = [
                'id' => $course->id,
                'title' => $course->title,
                'instructor' => $course->instructor->name,
                'category' => $course->category?->name,
                'price' => $course->price,
                'is_free' => $course->is_free,
                'status' => $course->status,
                'thumbnail' => $course->getFirstMediaUrl('thumbnail') ?: null,
            ];
        }

        return $this->paginatedJson($courses, $rows);
    }

    public function categories(): JsonResponse
    {
        return response()->json($this->categories->options());
    }
}
