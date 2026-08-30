<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Two published courses (one free, one paid) owned by the demo instructor.
 * Idempotent — guards on any course already existing. Depends on UserSeeder
 * and CategorySeeder having already run.
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        if (Course::query()->exists()) {
            return;
        }

        $instructor = User::query()->where('email', 'instructor@example.com')->firstOrFail();
        $category = Category::query()->where('slug', 'web-development')->firstOrFail();

        Course::factory()
            ->for($instructor, 'instructor')
            ->for($category, 'category')
            ->published()
            ->create([
                'title' => 'Modern React From Scratch',
                'slug' => Str::slug('Modern React From Scratch'),
                'description' => fake()->paragraphs(2, true),
                'is_free' => true,
                'price' => 0,
            ]);

        Course::factory()
            ->for($instructor, 'instructor')
            ->for($category, 'category')
            ->published()
            ->create([
                'title' => 'Laravel API Mastery',
                'slug' => Str::slug('Laravel API Mastery'),
                'description' => fake()->paragraphs(2, true),
                'is_free' => false,
                'price' => 249_000,
            ]);
    }
}
