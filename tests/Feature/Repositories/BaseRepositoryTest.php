<?php

use App\Models\User;
use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Eloquent\EloquentAuthRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Concrete repository used only by these tests, so BaseRepository can be
 * exercised against a real model with declared filters/sorts.
 */
class UserTestRepository extends BaseRepository
{
    /** @var list<string> */
    protected array $allowedFilters = ['name', 'email'];

    /** @var list<string> */
    protected array $allowedSorts = ['name', 'created_at'];

    /** @var list<string> */
    protected array $with = ['roles'];

    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}

function userRepo(): UserTestRepository
{
    return app(UserTestRepository::class);
}

test('EloquentAuthRepository inherits the base CRUD without redeclaring it', function () {
    $reflection = new ReflectionClass(EloquentAuthRepository::class);

    foreach (['all', 'find', 'findOrFail', 'create', 'update', 'updateWhere', 'delete', 'bulkUpdate', 'bulkDelete'] as $method) {
        expect($reflection->getMethod($method)->getDeclaringClass()->getName())->toBe(BaseRepository::class);
    }
});

test('create(), find() and findOrFail() round-trip a model', function () {
    $user = userRepo()->create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'password' => 'secret-secret',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and(userRepo()->findOrFail($user->id)->email)->toBe('ada@example.com')
        ->and(userRepo()->find(999_999))->toBeNull();
});

test('update() persists changes and returns the model', function () {
    $user = User::factory()->create(['name' => 'Before']);

    $returned = userRepo()->update($user, ['name' => 'After']);

    expect($returned)->toBeInstanceOf(User::class)
        ->and($user->fresh()->name)->toBe('After');
});

test('all() returns a paginator and honours perPage', function () {
    User::factory()->count(5)->create();

    $page = userRepo()->all(perPage: 2);

    expect($page)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($page->perPage())->toBe(2)
        ->and($page->total())->toBe(5);
});

test('all() applies forced $filters on top of the query', function () {
    User::factory()->create(['email' => 'keep@example.com']);
    User::factory()->count(3)->create();

    $page = userRepo()->all(['email' => 'keep@example.com']);

    expect($page->total())->toBe(1)
        ->and($page->items()[0]->email)->toBe('keep@example.com');
});

test('all() eager-loads the repository $with relations on every row', function () {
    User::factory()->count(2)->create();

    expect(userRepo()->all()->items()[0]->relationLoaded('roles'))->toBeTrue();
});

test('all() applies query-string filters through Spatie Query Builder', function () {
    User::factory()->create(['name' => 'Needle']);
    User::factory()->count(3)->create();

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['name' => 'Needle']]));

    expect(userRepo()->all()->total())->toBe(1);
});

test('updateWhere() updates matching rows in a transaction and returns the count', function () {
    User::factory()->count(3)->create(['name' => 'Old']);
    User::factory()->create(['name' => 'Other']);

    $affected = userRepo()->updateWhere(['name' => 'Old'], ['name' => 'New']);

    expect($affected)->toBe(3)
        ->and(User::query()->where('name', 'New')->count())->toBe(3);
});

test('updateWhere() supports closure conditions', function () {
    User::factory()->create(['email' => 'a@example.com']);
    User::factory()->create(['email' => 'b@example.com']);

    $affected = userRepo()->updateWhere(
        [fn (Builder $query) => $query->where('email', 'like', 'a@%')],
        ['name' => 'Matched'],
    );

    expect($affected)->toBe(1)
        ->and(User::query()->where('name', 'Matched')->count())->toBe(1);
});

test('bulkUpdate() applies the data to the given ids in a transaction and returns the count', function () {
    $users = User::factory()->count(4)->create(['name' => 'Unchanged']);
    $ids = $users->take(3)->pluck('id')->all();

    $affected = userRepo()->bulkUpdate($ids, ['name' => 'Bulk Updated']);

    expect($affected)->toBe(3)
        ->and(User::query()->where('name', 'Bulk Updated')->count())->toBe(3)
        ->and(User::query()->where('name', 'Unchanged')->count())->toBe(1);
});

test('bulkDelete() deletes the given ids in a transaction and returns the count', function () {
    $users = User::factory()->count(4)->create();
    $ids = $users->take(3)->pluck('id')->all();

    $deleted = userRepo()->bulkDelete($ids);

    expect($deleted)->toBe(3)
        ->and(User::query()->count())->toBe(1);
});
