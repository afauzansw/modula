<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'status' => 'active',
            'progress_percent' => 0,
            'last_lesson_id' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);
    }
}
