<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Two published courses (one free, one paid) owned by the demo instructor.
 * Idempotent — guards on any course already existing. Depends on UserSeeder
 * and CategorySeeder having already run.
 */
class CourseSeeder extends Seeder
{
    use ResolvesSeededRecords;

    public function run(): void
    {
        if (Course::query()->exists()) {
            return;
        }

        $now = now();
        $instructorId = $this->userByEmail('instructor@example.com')->id;
        $categoryId = Category::query()->where('slug', 'web-development')->firstOrFail()->id;

        Course::query()->insert([
            [
                'instructor_id' => $instructorId,
                'category_id' => $categoryId,
                'title' => 'Modern React From Scratch',
                'slug' => Str::slug('Modern React From Scratch'),
                'description' => 'Build production-grade React apps from first principles: components, hooks, state, routing, and data fetching.',
                'price' => 0,
                'is_free' => true,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'instructor_id' => $instructorId,
                'category_id' => $categoryId,
                'title' => 'Laravel API Mastery',
                'slug' => Str::slug('Laravel API Mastery'),
                'description' => 'Design, build, and secure REST and JSON APIs with Laravel — resources, versioning, auth, and testing.',
                'price' => 249_000,
                'is_free' => false,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
