<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory()->quiz(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'passing_score' => 70,
        ];
    }
}
