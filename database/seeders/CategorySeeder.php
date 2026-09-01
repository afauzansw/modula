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

        $now = now();

        Category::query()->insert([
            ['name' => 'Web Development', 'slug' => 'web-development', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Design', 'slug' => 'design', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
