<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'stars' => fake()->numberBetween(3, 5),
            'review_text' => fake()->paragraph(),
            'progress_percent_at_review' => fake()->numberBetween(30, 100),
            'last_lesson_id_at_review' => null,
            'edit_count' => 0,
        ];
    }

    public function edited(int $times = 1): static
    {
        return $this->state(['edit_count' => $times]);
    }
}
