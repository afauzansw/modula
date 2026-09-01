<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
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
    use ResolvesSeededRecords;

    public function run(): void
    {
        if (Enrollment::query()->exists()) {
            return;
        }

        $studentA = $this->userByEmail('student@example.com');
        $studentB = $this->userByEmail('student2@example.com');

        $freeCourse = Course::query()->where('slug', Str::slug('Modern React From Scratch'))->firstOrFail();
        $paidCourse = Course::query()->where('slug', Str::slug('Laravel API Mastery'))->firstOrFail();

        $this->enrollPartway($studentA, $freeCourse);
        $this->enrollCompleted($studentB, $paidCourse);
    }

    private function enrollPartway(User $student, Course $course): void
    {
        $lessons = $course->lessons()->orderBy('lessons.id')->get();
        $completed = $lessons->take(3);

        $this->markLessonsComplete($student, $completed);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => (int) round($completed->count() / max($lessons->count(), 1) * 100),
            'last_lesson_id' => $completed->last()?->id,
            'completed_at' => null,
        ]);
    }

    private function enrollCompleted(User $student, Course $course): void
    {
        $order = Order::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'order_number' => "ORD-{$student->id}-{$course->id}",
            'amount' => $course->price,
            'status' => 'paid',
            'gateway_ref' => "PAY-{$student->id}-{$course->id}",
            'expired_at' => now()->addDay(),
            'paid_at' => now(),
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'bank_transfer',
            'gateway_transaction_id' => (string) Str::uuid(),
            'amount' => $course->price,
            'raw_response' => ['transaction_status' => 'settlement', 'fraud_status' => 'accept'],
            'paid_at' => now(),
        ]);

        $this->markLessonsComplete($student, $course->lessons()->get());

        $lastLesson = $course->lessons()->orderByDesc('lessons.id')->first();

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress_percent' => 100,
            'last_lesson_id' => $lastLesson?->id,
            'completed_at' => now(),
        ]);

        $quiz = Quiz::query()
            ->whereHas('lesson.module', fn ($query) => $query->where('course_id', $course->id))
            ->first();

        if ($quiz !== null) {
            QuizAttempt::query()->create([
                'user_id' => $student->id,
                'quiz_id' => $quiz->id,
                'score' => 100,
                'passed' => true,
                'answers' => ['1' => '2', '3' => '6'],
                'attempted_at' => now(),
            ]);
        }

        $assignment = Assignment::query()
            ->whereHas('lesson.module', fn ($query) => $query->where('course_id', $course->id))
            ->first();

        if ($assignment !== null) {
            Submission::query()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'file_path' => "submissions/{$student->id}-{$assignment->id}.pdf",
                'grade' => 85,
                'feedback' => 'Solid work overall — tighten the error handling and resubmit if you want a higher mark.',
                'submitted_at' => now(),
                'graded_at' => now(),
            ]);
        }

        Certificate::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_number' => 'CERT-'.now()->year."-{$student->id}{$course->id}",
            'file_path' => "certificates/{$student->id}-{$course->id}.pdf",
            'issued_at' => now(),
        ]);
    }

    /**
     * @param  Collection<int, Lesson>  $lessons
     */
    private function markLessonsComplete(User $student, Collection $lessons): void
    {
        $now = now();

        LessonProgress::query()->insert($lessons
            ->map(fn (Lesson $lesson): array => [
                'user_id' => $student->id,
                'lesson_id' => $lesson->id,
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all());
    }
}
