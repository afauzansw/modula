<?php

namespace App\Repositories\Eloquent;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordChangeOtpNotification;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Auth-state data operations plus the OTP-gated password change flow. First real
 * consumer of BaseRepository — it inherits `all()`, `find()`, `create()`,
 * `update()`, `updateWhere()`, `bulkUpdate()`, `bulkDelete()`, etc. and only
 * adds the auth-specific methods below.
 */
class EloquentAuthRepository extends BaseRepository implements AuthRepositoryInterface
{
    private const OTP_TTL_MINUTES = 10;

    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  array{email: string, password: string, remember?: bool}  $credentials
     */
    public function login(array $credentials): bool
    {
        return Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            (bool) ($credentials['remember'] ?? false),
        );
    }

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(fn (): User => User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]));
    }

    public function profile(int $userId): User
    {
        return User::query()->findOrFail($userId);
    }

    public function sendPasswordChangeOtp(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $otp = OtpCode::query()->create([
            'user_id' => $user->id,
            'code' => (string) random_int(100000, 999999),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        $user->notify(new PasswordChangeOtpNotification($otp->code, self::OTP_TTL_MINUTES));
    }

    public function verifyOtpAndChangePassword(int $userId, string $otp, string $newPassword): bool
    {
        return DB::transaction(function () use ($userId, $otp, $newPassword): bool {
            $otpCode = OtpCode::query()
                ->where('user_id', $userId)
                ->where('code', $otp)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($otpCode === null) {
                return false;
            }

            User::query()->findOrFail($userId)->update(['password' => Hash::make($newPassword)]);

            $otpCode->update(['used_at' => now()]);

            return true;
        });
    }
}
