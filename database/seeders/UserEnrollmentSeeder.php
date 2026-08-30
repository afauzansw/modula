<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * A student's course journey: enrollment, lesson progress, the paid-checkout
 * trail (order + payment), a quiz attempt, a graded submission, and a
 * certificate. Kept as a single seeder because these all hang off "a
 * student's enrollment in a course" and reference each other directly.
 * Ratings are seeded separately (see RatingSeeder) since they only need the
 * enrollment's resulting progress snapshot, not the journey itself.
 *
 * Idempotent — guards on any enrollment already existing. Depends on
 * UserSeeder, CourseSeeder, and ModuleSeeder having already run.
 */
class UserEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        if (Enrollment::query()->exists()) {
            return;
        }

        $studentA = User::query()->where('email', 'student@example.com')->firstOrFail();
        $studentB = User::query()->where('email', 'student2@example.com')->firstOrFail();

        $freeCourse = Course::query()->where('slug', Str::slug('Modern React From Scratch'))->firstOrFail();
        $paidCourse = Course::query()->where('slug', Str::slug('Laravel API Mastery'))->firstOrFail();

        $this->enrollPartway($studentA, $freeCourse);
        $this->enrollCompleted($studentB, $paidCourse);
    }

    private function enrollPartway(User $student, Course $course): void
    {
        $lessons = $course->lessons()->orderBy('lessons.id')->get();
        $completed = $lessons->take(3);

        foreach ($completed as $lesson) {
            LessonProgress::factory()->for($student, 'user')->for($lesson)->create();
        }

        Enrollment::factory()->for($student, 'user')->for($course)->create([
            'progress_percent' => (int) round($completed->count() / max($lessons->count(), 1) * 100),
            'last_lesson_id' => $completed->last()?->id,
        ]);
    }

    private function enrollCompleted(User $student, Course $course): void
    {
        $order = Order::factory()->for($student, 'user')->for($course)->paid()->create(['amount' => $course->price]);
        Payment::factory()->for($order)->create(['amount' => $course->price]);

        foreach ($course->lessons()->get() as $lesson) {
            LessonProgress::factory()->for($student, 'user')->for($lesson)->create();
        }

        $lastLesson = $course->lessons()->orderByDesc('lessons.id')->first();

        Enrollment::factory()->for($student, 'user')->for($course)->completed()->create([
            'last_lesson_id' => $lastLesson?->id,
        ]);

        $quiz = Quiz::query()
            ->whereHas('lesson.module', fn ($query) => $query->where('course_id', $course->id))
            ->first();
        $quiz && QuizAttempt::factory()->for($student, 'user')->for($quiz)->passed()->create();

        $assignment = Assignment::query()
            ->whereHas('lesson.module', fn ($query) => $query->where('course_id', $course->id))
            ->first();
        $assignment && Submission::factory()->for($assignment)->for($student, 'user')->graded()->create();

        Certificate::factory()->for($student, 'user')->for($course)->create();
    }
}
