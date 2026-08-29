<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory()->assignment(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'description' => fake()->paragraph(),
            'due_date' => now()->addWeek(),
        ];
    }
}
