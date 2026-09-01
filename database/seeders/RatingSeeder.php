<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Rating;
use App\Models\User;
use Carbon\CarbonImmutable;
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
    use ResolvesSeededRecords;

    public function run(): void
    {
        if (Rating::query()->exists()) {
            return;
        }

        $now = now();

        $studentA = $this->userByEmail('student@example.com');
        $studentB = $this->userByEmail('student2@example.com');

        $freeCourse = Course::query()->where('slug', Str::slug('Modern React From Scratch'))->firstOrFail();
        $paidCourse = Course::query()->where('slug', Str::slug('Laravel API Mastery'))->firstOrFail();

        Rating::query()->insert([
            $this->ratingRow($studentA, $freeCourse, stars: 5, now: $now),
            $this->ratingRow($studentB, $paidCourse, stars: 4, now: $now),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function ratingRow(User $student, Course $course, int $stars, CarbonImmutable $now): array
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        return [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'stars' => $stars,
            'review_text' => 'Clear, well-paced, and genuinely useful — recommended.',
            'progress_percent_at_review' => $enrollment->progress_percent,
            'last_lesson_id_at_review' => $enrollment->last_lesson_id,
            'edit_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
