<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The accounts used across the other seeders and for manual/local login.
 * Idempotent — guards on any user already existing, so it's safe to re-run
 * and to seed standalone. Depends on RolePermissionSeeder having run.
 */
class UserSeeder extends Seeder
{
    /** email => [name, role|null] */
    private const ACCOUNTS = [
        'test@example.com' => ['Test User', null],
        'instructor@example.com' => ['Iman Instructor', 'instructor'],
        'student@example.com' => ['Sari Student', 'student'],
        'student2@example.com' => ['Budi Student', 'student'],
        'admin@example.com' => ['Ada Admin', 'admin'],
    ];

    public function run(): void
    {
        if (User::query()->exists()) {
            return;
        }

        $now = now();
        $password = Hash::make('password');

        User::query()->insert(collect(self::ACCOUNTS)
            ->map(fn (array $account, string $email): array => [
                'name' => $account[0],
                'email' => $email,
                'password' => $password,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all());

        foreach (self::ACCOUNTS as $email => $account) {
            if ($account[1] !== null) {
                User::query()->where('email', $email)->firstOrFail()->syncRoles([$account[1]]);
            }
        }
    }
}
