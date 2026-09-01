<?php

namespace Database\Seeders;

use App\Models\User;

/**
 * Shared user lookups for seeders. This project has no model factories —
 * seeders build rows with `Model::query()->create([...])` and resolve
 * relations by querying for a row an earlier seeder created.
 */
trait ResolvesSeededRecords
{
    /** The demo account with this email (must already be seeded). */
    protected function userByEmail(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }

    /**
     * A random user holding the given role — the pattern to use when a seeder
     * bulk-creates rows that just need "an instructor" / "a student".
     */
    protected function randomUserIdWithRole(string $role): int
    {
        return User::whereHas('roles', fn ($query) => $query->where('name', $role))
            ->inRandomOrder()
            ->firstOrFail()
            ->id;
    }
}
