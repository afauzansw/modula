<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\Order;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| This project has no model factories. Tests build rows with these plain
| helpers (or `Model::query()->create([...])` directly); override any column
| inline — `createUser(['email_verified_at' => null])`. States that used to be
| factory methods are just override arrays now.
|
*/

/**
 * A verified user with the shared 'password'. `email_verified_at` /
 * `two_factor_*` are force-filled (they're outside the model's `$fillable`).
 *
 * @param  array<string, mixed>  $attributes
 */
function createUser(array $attributes = []): User
{
    return User::forceCreate([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'email_verified_at' => now(),
        ...$attributes,
    ]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createCategory(array $attributes = []): Category
{
    $name = Str::title(fake()->unique()->words(2, true));

    return Category::query()->create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
        ...$attributes,
    ]);
}

/**
 * A draft, free course. Pass `instructor_id` / `category_id` to attach existing
 * rows, otherwise fresh ones are created.
 *
 * @param  array<string, mixed>  $attributes
 */
function createCourse(array $attributes = []): Course
{
    $title = fake()->unique()->sentence(4);

    $attributes['instructor_id'] ??= createUser()->id;

    // category_id is nullable — only default it when the caller left it out.
    if (! array_key_exists('category_id', $attributes)) {
        $attributes['category_id'] = createCategory()->id;
    }

    return Course::query()->create([
        'title' => $title,
        'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
        'description' => fake()->paragraph(),
        'thumbnail_path' => null,
        'price' => 0,
        'is_free' => true,
        'status' => 'draft',
        'certificate_template_path' => null,
        ...$attributes,
    ]);
}

/**
 * A pending order. Pass `user_id` / `course_id` to attach existing rows.
 *
 * @param  array<string, mixed>  $attributes
 */
function createOrder(array $attributes = []): Order
{
    $attributes['user_id'] ??= createUser()->id;
    $attributes['course_id'] ??= createCourse()->id;

    return Order::query()->create([
        'order_number' => 'ORD-'.Str::upper(Str::random(10)),
        'amount' => 149_000,
        'status' => 'pending',
        'gateway_ref' => null,
        'expired_at' => now()->addDay(),
        'paid_at' => null,
        ...$attributes,
    ]);
}

/**
 * An unexpired, unused OTP code for the given (or a fresh) user.
 *
 * @param  array<string, mixed>  $attributes
 */
function createOtpCode(array $attributes = []): OtpCode
{
    $attributes['user_id'] ??= createUser()->id;

    return OtpCode::query()->create([
        'code' => (string) fake()->numberBetween(100_000, 999_999),
        'expires_at' => now()->addMinutes(10),
        'used_at' => null,
        ...$attributes,
    ]);
}
