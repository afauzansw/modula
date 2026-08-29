<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => rtrim(fake()->sentence(4), '.'),
            'type' => 'text',
            'content' => fake()->paragraphs(3, true),
            'video_path' => null,
            'video_duration_seconds' => null,
            'order' => fake()->numberBetween(0, 20),
            'is_preview' => false,
        ];
    }

    public function video(): static
    {
        return $this->state([
            'type' => 'video',
            'content' => null,
            'video_path' => 'videos/'.fake()->uuid().'.mp4',
            'video_duration_seconds' => fake()->numberBetween(60, 3600),
        ]);
    }

    public function quiz(): static
    {
        return $this->state(['type' => 'quiz', 'content' => null]);
    }

    public function assignment(): static
    {
        return $this->state(['type' => 'assignment', 'content' => null]);
    }

    public function preview(): static
    {
        return $this->state(['is_preview' => true]);
    }
}
