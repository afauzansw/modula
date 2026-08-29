<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Option;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Rating;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Builds one realistic slice of the LMS: roles, an instructor, students, two
 * published courses (one free, one paid) with modules/lessons/quizzes/assignments,
 * and students at different points in their journey (partway, completed + certified).
 *
 * Intended for `php artisan migrate:fresh --seed`. Roles and demo accounts are
 * created idempotently; course content is only built when no courses exist yet.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();

        $instructor = $this->accountFor('instructor@example.com', 'Iman Instructor', 'instructor');
        $studentA = $this->accountFor('student@example.com', 'Sari Student', 'student');
        $studentB = $this->accountFor('student2@example.com', 'Budi Student', 'student');
        $this->accountFor('admin@example.com', 'Ada Admin', 'admin');

        if (Course::query()->exists()) {
            return;
        }

        $webDev = Category::factory()->create(['name' => 'Web Development', 'slug' => 'web-development']);
        Category::factory()->create(['name' => 'Design', 'slug' => 'design']);

        $freeCourse = $this->buildCourse($instructor, $webDev, 'Modern React From Scratch', isFree: true);
        $paidCourse = $this->buildCourse($instructor, $webDev, 'Laravel API Mastery', isFree: false, price: 249_000);

        $this->enrollPartway($studentA, $freeCourse);
        $this->enrollCompleted($studentB, $paidCourse);
    }

    private function seedRoles(): void
    {
        foreach (['admin', 'instructor', 'student'] as $role) {
            Role::findOrCreate($role);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function accountFor(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'email_verified_at' => now()],
        );

        $user->syncRoles([$role]);

        return $user;
    }

    private function buildCourse(User $instructor, Category $category, string $title, bool $isFree, int $price = 0): Course
    {
        $course = Course::factory()
            ->for($instructor, 'instructor')
            ->for($category, 'category')
            ->published()
            ->create([
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => fake()->paragraphs(2, true),
                'is_free' => $isFree,
                'price' => $isFree ? 0 : $price,
            ]);

        $lastModule = null;

        foreach (range(1, 3) as $number) {
            $module = Module::factory()->for($course)->create([
                'title' => "Module {$number}",
                'order' => $number,
            ]);
            $lastModule = $module;

            Lesson::factory()->for($module)->create(['title' => "Lesson {$number}.1 — Concepts", 'order' => 1]);
            Lesson::factory()->for($module)->video()->create(['title' => "Lesson {$number}.2 — Walkthrough", 'order' => 2]);

            $quizLesson = Lesson::factory()->for($module)->quiz()->create(['title' => "Lesson {$number}.3 — Quiz", 'order' => 3]);
            $quiz = Quiz::factory()->for($quizLesson)->create(['title' => "Module {$number} Quiz"]);
            $question = Question::factory()->for($quiz)->create(['question_text' => 'Which statement is correct?', 'order' => 1]);
            Option::factory()->for($question)->correct()->create(['option_text' => 'The correct answer', 'order' => 1]);
            Option::factory()->for($question)->count(2)->create();
        }

        $assignmentLesson = Lesson::factory()->for($lastModule)->assignment()->create([
            'title' => 'Final Assignment',
            'order' => 4,
        ]);
        Assignment::factory()->for($assignmentLesson)->create(['title' => 'Build a small project']);

        return $course;
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

        Rating::factory()->for($student, 'user')->for($course)->create([
            'stars' => 5,
            'progress_percent_at_review' => 30,
            'last_lesson_id_at_review' => $completed->last()?->id,
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

        Rating::factory()->for($student, 'user')->for($course)->create([
            'stars' => 4,
            'progress_percent_at_review' => 100,
            'last_lesson_id_at_review' => $lastLesson?->id,
        ]);

        Certificate::factory()->for($student, 'user')->for($course)->create();
    }
}
