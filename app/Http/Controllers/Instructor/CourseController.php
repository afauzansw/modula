<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\BulkDestroyCourseRequest;
use App\Http\Requests\Instructor\BulkUpdateCourseStatusRequest;
use App\Http\Requests\Instructor\CourseRequest;
use App\Models\InstructorCourse;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\InstructorCourseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function __construct(
        private readonly InstructorCourseRepositoryInterface $courses,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function index(): Response
    {
        return Inertia::render('instructor/courses/index');
    }

    public function fetch(): JsonResponse
    {
        $courses = $this->courses->all();

        $rows = [];

        foreach ($courses->items() as $course) {
            if (! $course instanceof InstructorCourse) {
                continue;
            }

            $rows[] = [
                'id' => $course->id,
                'title' => $course->title,
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

    public function create(): Response
    {
        return Inertia::render('instructor/courses/create', [
            'categories' => $this->categories->options(),
        ]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $this->courses->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Course created.')]);

        return redirect()->route('instructor.courses.index');
    }

    public function edit(InstructorCourse $course): Response
    {
        return Inertia::render('instructor/courses/edit', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'category_id' => $course->category_id,
                'description' => $course->description,
                'price' => $course->price,
                'is_free' => $course->is_free,
                'status' => $course->status,
                'thumbnail' => $course->getFirstMediaUrl('thumbnail') ?: null,
            ],
            'categories' => $this->categories->options(),
        ]);
    }

    public function update(CourseRequest $request, InstructorCourse $course): RedirectResponse
    {
        $this->courses->update($course, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Course updated.')]);

        return redirect()->route('instructor.courses.index');
    }

    public function destroy(InstructorCourse $course): RedirectResponse
    {
        $this->courses->delete($course);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Course deleted.')]);

        return redirect()->route('instructor.courses.index');
    }

    public function bulkDestroy(BulkDestroyCourseRequest $request): RedirectResponse
    {
        $deleted = $this->courses->bulkDelete($request->validated('ids'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice('{0}No courses deleted.|{1}:count course deleted.|[2,*]:count courses deleted.', $deleted, ['count' => $deleted]),
        ]);

        return redirect()->route('instructor.courses.index');
    }

    public function bulkUpdateStatus(BulkUpdateCourseStatusRequest $request): RedirectResponse
    {
        $status = $request->validated('status');
        $updated = $this->courses->bulkUpdate($request->validated('ids'), ['status' => $status]);

        $verb = $status === 'published' ? 'published' : 'unpublished';

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => trans_choice("{0}No courses {$verb}.|{1}:count course {$verb}.|[2,*]:count courses {$verb}.", $updated, ['count' => $updated]),
        ]);

        return redirect()->route('instructor.courses.index');
    }
}
