<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Idempotent — guards on any category already existing, so it's safe to
 * re-run and to seed standalone.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        Category::factory()->create(['name' => 'Web Development', 'slug' => 'web-development']);
        Category::factory()->create(['name' => 'Design', 'slug' => 'design']);
    }
}
