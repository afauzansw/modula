<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $score = fake()->numberBetween(0, 100);

        return [
            'user_id' => User::factory(),
            'quiz_id' => Quiz::factory(),
            'score' => $score,
            'passed' => $score >= 70,
            'answers' => ['1' => '2', '3' => '6'],
            'attempted_at' => now(),
        ];
    }

    public function passed(): static
    {
        return $this->state(['score' => 100, 'passed' => true]);
    }

    public function failed(): static
    {
        return $this->state(['score' => 20, 'passed' => false]);
    }
}
