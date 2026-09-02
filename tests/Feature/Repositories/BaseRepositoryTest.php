<?php

use App\Models\User;
use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Eloquent\EloquentAuthRepository;
use App\Repositories\PaginateQuery;
use App\Repositories\SpatieQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Exceptions\InvalidSortQuery;

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
    $user = createUser(['name' => 'Before']);

    $returned = userRepo()->update($user, ['name' => 'After']);

    expect($returned)->toBeInstanceOf(User::class)
        ->and($user->fresh()->name)->toBe('After');
});

test('all() returns a paginator and honours PaginateQuery perPage', function () {
    collect(range(1, 5))->map(fn () => createUser());

    $page = userRepo()->all(paginate: new PaginateQuery(perPage: 2));

    expect($page)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($page->perPage())->toBe(2)
        ->and($page->total())->toBe(5)
        ->and($page->lastPage())->toBe(3);
});

test('all() with withPaginate: false puts every row on a single page', function () {
    collect(range(1, 5))->map(fn () => createUser());

    $page = userRepo()->all(paginate: new PaginateQuery(withPaginate: false));

    expect($page->total())->toBe(5)
        ->and($page->lastPage())->toBe(1)
        ->and($page->items())->toHaveCount(5);
});

test('a non-empty SpatieQuery is the filter allow-list for that call', function () {
    createUser(['name' => 'Keep', 'email' => 'keep@example.com']);
    collect(range(1, 3))->map(fn () => createUser());

    // UserTestRepository allows [name, email]; this call narrows the request to [email]
    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['email' => 'keep@example.com']]));
    expect(userRepo()->all(new SpatieQuery(filters: ['email']))->total())->toBe(1);

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['name' => 'Keep']]));
    expect(fn () => userRepo()->all(new SpatieQuery(filters: ['email'])))->toThrow(InvalidFilterQuery::class);
});

test('a non-empty SpatieQuery is the sort allow-list for that call', function () {
    createUser(['email' => 'b@example.com']);
    createUser(['email' => 'a@example.com']);
    createUser(['email' => 'c@example.com']);

    // the repo's $allowedSorts has no 'email'; naming it here lets the request use it
    $this->app->instance('request', Request::create('/', 'GET', ['sort' => '-email']));
    $emails = collect(userRepo()->all(new SpatieQuery(sorts: ['email']))->items())->pluck('email')->all();
    expect($emails)->toBe(['c@example.com', 'b@example.com', 'a@example.com']);

    $this->app->instance('request', Request::create('/', 'GET', ['sort' => 'name']));
    expect(fn () => userRepo()->all(new SpatieQuery(sorts: ['email'])))->toThrow(InvalidSortQuery::class);
});

test('all() eager-loads the repository $with relations on every row', function () {
    collect(range(1, 2))->map(fn () => createUser());

    expect(userRepo()->all()->items()[0]->relationLoaded('roles'))->toBeTrue();
});

test('all() applies query-string filters through Spatie Query Builder', function () {
    createUser(['name' => 'Needle']);
    collect(range(1, 3))->map(fn () => createUser());

    $this->app->instance('request', Request::create('/', 'GET', ['filter' => ['name' => 'Needle']]));

    expect(userRepo()->all()->total())->toBe(1);
});

test('updateWhere() updates matching rows in a transaction and returns the count', function () {
    collect(range(1, 3))->map(fn () => createUser(['name' => 'Old']));
    createUser(['name' => 'Other']);

    $affected = userRepo()->updateWhere(['name' => 'Old'], ['name' => 'New']);

    expect($affected)->toBe(3)
        ->and(User::query()->where('name', 'New')->count())->toBe(3);
});

test('updateWhere() supports closure conditions', function () {
    createUser(['email' => 'a@example.com']);
    createUser(['email' => 'b@example.com']);

    $affected = userRepo()->updateWhere(
        [fn (Builder $query) => $query->where('email', 'like', 'a@%')],
        ['name' => 'Matched'],
    );

    expect($affected)->toBe(1)
        ->and(User::query()->where('name', 'Matched')->count())->toBe(1);
});

test('bulkUpdate() applies the data to the given ids in a transaction and returns the count', function () {
    $users = collect(range(1, 4))->map(fn () => createUser(['name' => 'Unchanged']));
    $ids = $users->take(3)->pluck('id')->all();

    $affected = userRepo()->bulkUpdate($ids, ['name' => 'Bulk Updated']);

    expect($affected)->toBe(3)
        ->and(User::query()->where('name', 'Bulk Updated')->count())->toBe(3)
        ->and(User::query()->where('name', 'Unchanged')->count())->toBe(1);
});

test('bulkDelete() deletes the given ids in a transaction and returns the count', function () {
    $users = collect(range(1, 4))->map(fn () => createUser());
    $ids = $users->take(3)->pluck('id')->all();

    $deleted = userRepo()->bulkDelete($ids);

    expect($deleted)->toBe(3)
        ->and(User::query()->count())->toBe(1);
});
