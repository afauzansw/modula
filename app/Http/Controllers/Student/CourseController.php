<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function __construct(private readonly EnrollmentRepositoryInterface $enrollments) {}

    public function index(Request $request): Response
    {
        $courses = $this->enrollments
            ->forStudent($request->user()->id)
            ->map(fn (Enrollment $enrollment): array => [
                'id' => $enrollment->course->id,
                'title' => $enrollment->course->title,
                'instructor' => $enrollment->course->instructor->name,
                'thumbnail' => $enrollment->course->getFirstMediaUrl('thumbnail') ?: null,
                'progress_percent' => $enrollment->progress_percent,
                'status' => $enrollment->status,
            ]);

        return Inertia::render('student/courses', ['courses' => $courses]);
    }
}
