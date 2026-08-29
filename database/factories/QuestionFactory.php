<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'question_text' => rtrim(fake()->sentence(), '.').'?',
            'type' => 'multiple_choice',
            'order' => fake()->numberBetween(0, 20),
        ];
    }

    public function trueFalse(): static
    {
        return $this->state(['type' => 'true_false']);
    }

    public function shortAnswer(): static
    {
        return $this->state(['type' => 'short_answer']);
    }
}
