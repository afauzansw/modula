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
 * Rows whose id is needed immediately by children use `create()`; flat
 * same-type batches (the two content lessons, the answer options) are
 * bulk-`insert()`ed.
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
        $now = now();
        $lastModule = null;

        foreach (range(1, 3) as $number) {
            $module = Module::query()->create([
                'course_id' => $course->id,
                'title' => "Module {$number}",
                'order' => $number,
            ]);
            $lastModule = $module;

            Lesson::query()->insert([
                [
                    'module_id' => $module->id,
                    'title' => "Lesson {$number}.1 — Concepts",
                    'type' => 'text',
                    'content' => 'A written walkthrough of the core concepts for this module.',
                    'video_path' => null,
                    'video_duration_seconds' => null,
                    'order' => 1,
                    'is_preview' => $number === 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'module_id' => $module->id,
                    'title' => "Lesson {$number}.2 — Walkthrough",
                    'type' => 'video',
                    'content' => null,
                    'video_path' => "videos/module-{$number}-walkthrough.mp4",
                    'video_duration_seconds' => 720,
                    'order' => 2,
                    'is_preview' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $quizLesson = Lesson::query()->create([
                'module_id' => $module->id,
                'title' => "Lesson {$number}.3 — Quiz",
                'type' => 'quiz',
                'content' => null,
                'video_path' => null,
                'video_duration_seconds' => null,
                'order' => 3,
                'is_preview' => false,
            ]);

            $quiz = Quiz::query()->create([
                'lesson_id' => $quizLesson->id,
                'title' => "Module {$number} Quiz",
                'passing_score' => 70,
            ]);

            $question = Question::query()->create([
                'quiz_id' => $quiz->id,
                'question_text' => 'Which statement is correct?',
                'type' => 'multiple_choice',
                'order' => 1,
            ]);

            Option::query()->insert([
                ['question_id' => $question->id, 'option_text' => 'The correct answer', 'is_correct' => true, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['question_id' => $question->id, 'option_text' => 'A plausible distractor', 'is_correct' => false, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['question_id' => $question->id, 'option_text' => 'Another distractor', 'is_correct' => false, 'order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        $assignmentLesson = Lesson::query()->create([
            'module_id' => $lastModule->id,
            'title' => 'Final Assignment',
            'type' => 'assignment',
            'content' => null,
            'video_path' => null,
            'video_duration_seconds' => null,
            'order' => 4,
            'is_preview' => false,
        ]);

        Assignment::query()->create([
            'lesson_id' => $assignmentLesson->id,
            'title' => 'Build a small project',
            'description' => 'Apply everything from this course by shipping a small end-to-end project.',
            'due_date' => now()->addWeek(),
        ]);
    }
}
