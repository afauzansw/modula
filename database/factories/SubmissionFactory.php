<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'user_id' => User::factory(),
            'file_path' => 'submissions/'.Str::lower(Str::random(16)).'.pdf',
            'grade' => null,
            'feedback' => null,
            'submitted_at' => now(),
            'graded_at' => null,
        ];
    }

    public function graded(int $grade = 85): static
    {
        return $this->state([
            'grade' => $grade,
            'feedback' => fake()->sentence(),
            'graded_at' => now(),
        ]);
    }
}
