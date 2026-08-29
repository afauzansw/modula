<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Option>
 */
class OptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'option_text' => rtrim(fake()->sentence(3), '.'),
            'is_correct' => false,
            'order' => fake()->numberBetween(0, 5),
        ];
    }

    public function correct(): static
    {
        return $this->state(['is_correct' => true]);
    }
}
