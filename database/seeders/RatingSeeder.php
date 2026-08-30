<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * One rating per demo student, snapshotting each student's enrollment
 * progress at review time (`progress_percent_at_review` /
 * `last_lesson_id_at_review` — see PROJECT_CONTEXT.md §8).
 *
 * Idempotent — guards on any rating already existing. Depends on UserSeeder,
 * CourseSeeder, and UserEnrollmentSeeder having already run.
 */
class RatingSeeder extends Seeder
{
    public function run(): void
    {
        if (Rating::query()->exists()) {
            return;
        }

        $studentA = User::query()->where('email', 'student@example.com')->firstOrFail();
        $studentB = User::query()->where('email', 'student2@example.com')->firstOrFail();

        $freeCourse = Course::query()->where('slug', Str::slug('Modern React From Scratch'))->firstOrFail();
        $paidCourse = Course::query()->where('slug', Str::slug('Laravel API Mastery'))->firstOrFail();

        $this->rate($studentA, $freeCourse, stars: 5);
        $this->rate($studentB, $paidCourse, stars: 4);
    }

    private function rate(User $student, Course $course, int $stars): void
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        Rating::factory()->for($student, 'user')->for($course)->create([
            'stars' => $stars,
            'progress_percent_at_review' => $enrollment->progress_percent,
            'last_lesson_id_at_review' => $enrollment->last_lesson_id,
        ]);
    }
}
