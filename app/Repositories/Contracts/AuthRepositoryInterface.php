<?php

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * Authentication-related data operations. Deliberately standalone (it does not
 * extend BaseRepositoryInterface) — it is a thin, data-oriented wrapper around
 * Laravel's auth state plus the OTP-gated password change flow, not a set of
 * CRUD operations on a domain entity.
 */
interface AuthRepositoryInterface
{
    /**
     * @param  array{email: string, password: string, remember?: bool}  $credentials
     */
    public function login(array $credentials): bool;

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User;

    public function profile(int $userId): User;

    /**
     * Generate + persist a one-time code and dispatch it to the user.
     */
    public function sendPasswordChangeOtp(int $userId): void;

    /**
     * Verify the code (matches, unexpired, unused) and, if valid, change the
     * password and consume the code. Returns false without side effects otherwise.
     */
    public function verifyOtpAndChangePassword(int $userId, string $otp, string $newPassword): bool;
}
