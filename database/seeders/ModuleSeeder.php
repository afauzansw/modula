<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

/**
 * Builds each course's content tree in one place: modules, lessons (video,
 * text, quiz, assignment), and the quiz/assignment records they carry.
 * Kept as a single seeder because these tables are structurally nested —
 * a Lesson doesn't exist without a Module, a Quiz doesn't exist without a
 * quiz-type Lesson, and so on.
 *
 * Idempotent — guards on any module already existing. Depends on
 * CourseSeeder having already run.
 */
class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        if (Module::query()->exists()) {
            return;
        }

        foreach (Course::all() as $course) {
            $this->buildModules($course);
        }
    }

    private function buildModules(Course $course): void
    {
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
    }
}
