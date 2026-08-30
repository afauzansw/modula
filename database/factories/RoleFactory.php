<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::title(rtrim(fake()->unique()->sentence(2), '.')),
            'guard_name' => 'web',
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(['is_system' => true]);
    }
}
