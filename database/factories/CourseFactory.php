<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(4), '.');

        return [
            'instructor_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'description' => fake()->paragraph(),
            'thumbnail_path' => null,
            'price' => 0,
            'is_free' => true,
            'status' => 'draft',
            'certificate_template_path' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }

    public function archived(): static
    {
        return $this->state(['status' => 'archived']);
    }

    public function paid(int $price = 149_000): static
    {
        return $this->state(['is_free' => false, 'price' => $price]);
    }
}
