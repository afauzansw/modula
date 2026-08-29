<?php

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordChangeOtpNotification;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Eloquent\EloquentAuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

/**
 * Stands in for a controller: constructor-injects the interface exactly the way
 * Laravel resolves controller dependencies.
 */
class RepositoryDependentController
{
    public function __construct(public AuthRepositoryInterface $auth) {}
}

function authRepo(): AuthRepositoryInterface
{
    return app(AuthRepositoryInterface::class);
}

test('the auth repository interface resolves to the Eloquent implementation', function () {
    expect(authRepo())->toBeInstanceOf(EloquentAuthRepository::class);
});

test('the auth repository is injected into a constructor the way a controller resolves it', function () {
    expect(app(RepositoryDependentController::class)->auth)->toBeInstanceOf(EloquentAuthRepository::class);
});

test('register() then login() then profile() works end to end', function () {
    $user = authRepo()->register([
        'name' => 'Sam',
        'email' => 'sam@example.com',
        'password' => 'password123',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and(Hash::check('password123', $user->password))->toBeTrue();

    expect(authRepo()->login(['email' => 'sam@example.com', 'password' => 'password123']))->toBeTrue();
    $this->assertAuthenticatedAs($user);

    expect(authRepo()->login(['email' => 'sam@example.com', 'password' => 'wrong-password']))->toBeFalse();

    expect(authRepo()->profile($user->id)->email)->toBe('sam@example.com');
});

test('sendPasswordChangeOtp() stores an unused, unexpired code and dispatches the notification', function () {
    Notification::fake();
    $user = User::factory()->create();

    authRepo()->sendPasswordChangeOtp($user->id);

    $otp = OtpCode::query()->where('user_id', $user->id)->sole();
    expect($otp->used_at)->toBeNull()
        ->and($otp->expires_at->isFuture())->toBeTrue();

    Notification::assertSentTo(
        $user,
        PasswordChangeOtpNotification::class,
        fn (PasswordChangeOtpNotification $notification) => $notification->code === $otp->code,
    );
});

test('verifyOtpAndChangePassword() changes the password and consumes the code', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    $otp = OtpCode::factory()->for($user)->create(['code' => '123456']);

    $result = authRepo()->verifyOtpAndChangePassword($user->id, '123456', 'brand-new-password');

    expect($result)->toBeTrue()
        ->and(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue()
        ->and($otp->fresh()->used_at)->not->toBeNull();
});

test('verifyOtpAndChangePassword() rejects a wrong code and changes nothing', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    OtpCode::factory()->for($user)->create(['code' => '123456']);

    $result = authRepo()->verifyOtpAndChangePassword($user->id, '999999', 'attempted-password');

    expect($result)->toBeFalse()
        ->and(Hash::check('old-password', $user->fresh()->password))->toBeTrue()
        ->and(OtpCode::query()->whereNotNull('used_at')->count())->toBe(0);
});

test('verifyOtpAndChangePassword() rejects an expired code and changes nothing', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    OtpCode::factory()->for($user)->expired()->create(['code' => '123456']);

    expect(authRepo()->verifyOtpAndChangePassword($user->id, '123456', 'attempted-password'))->toBeFalse()
        ->and(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

test('verifyOtpAndChangePassword() rejects an already-used code', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    OtpCode::factory()->for($user)->used()->create(['code' => '123456']);

    expect(authRepo()->verifyOtpAndChangePassword($user->id, '123456', 'attempted-password'))->toBeFalse();
});
