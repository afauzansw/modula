<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The accounts used across the other seeders and for manual/local login.
 * Idempotent (firstOrCreate by email) — safe to re-run and to seed standalone.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->accountFor('test@example.com', 'Test User');
        $this->accountFor('instructor@example.com', 'Iman Instructor', 'instructor');
        $this->accountFor('student@example.com', 'Sari Student', 'student');
        $this->accountFor('student2@example.com', 'Budi Student', 'student');
        $this->accountFor('admin@example.com', 'Ada Admin', 'admin');
    }

    private function accountFor(string $email, string $name, ?string $role = null): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'email_verified_at' => now()],
        );

        if ($role !== null) {
            $user->syncRoles([$role]);
        }
    }
}
