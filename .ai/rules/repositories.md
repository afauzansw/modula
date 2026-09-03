---
paths:
  - 'app/Repositories/**'
  - 'app/Http/Controllers/**'
  - 'app/Http/Requests/**'
---

# Repository pattern: BaseRepository/EloquentAuthRepository are the reference, everything else is silent

## Comments live only on BaseRepository and EloquentAuthRepository
Those two are the documented reference implementations — read them to
understand *why*. A concrete repository (`Eloquent*Repository`) and its
controller carry **no docblocks or inline comments** — just properties, the
constructor, and method/hook overrides. If a decision needs explaining, it's
already explained on `BaseRepository`; don't repeat it downstream.

## afterCreate() / afterUpdate() hooks, not create()/update() overrides
`BaseRepository::create()` and `update()` wrap the write in `DB::transaction()`
and call a no-op hook afterwards:

```php
public function create(array $data): Model
{
    return DB::transaction(function () use ($data): Model {
        $model = $this->model->newQuery()->create($data);
        $this->afterCreate($model, $data);
        return $model;
    });
}

protected function afterCreate(Model $model, array $data): void {}
```

`update()` mirrors this with `afterUpdate()`. A concrete repository that needs
to persist something alongside the main row — sync a pivot, create child rows
— overrides the **hook**, not `create()`/`update()` themselves. See
`EloquentRoleRepository::afterCreate()` / `afterUpdate()` (syncs the role's
`permissions` from the data array) for the pattern.

- Data a hook needs must arrive through the `$data` array the controller
  passed to `create()`/`update()` — never read `request()` inside a
  repository. That's what keeps repositories callable from seeders, jobs,
  console commands, and tests without faking an HTTP context.
- `$model` in the hook is typed `Model` (the base signature); narrow with
  `if ($model instanceof Role)` before calling a model-specific method.
- No `@param`/`@var` docblocks are needed on an overriding hook or a
  redeclared property to keep PHPStan (larastan level 7) clean — it resolves
  the types from BaseRepository's own declarations even when a child
  repository redeclares the property with a different default, or overrides
  a documented method without repeating its docblock. Confirmed empirically;
  don't re-add them "to be safe" (that would also violate the no-comments rule
  above).

## A model written to via create()/update() needs an explicit #[Fillable]
Every project model declares `#[Fillable([...])]` (see `Course`, `Category`).
This is what makes the hook pattern above safe: `create()`/`update()` pass the
*whole* `$data` array — including keys that aren't columns (like `permissions`
for a role) — straight to Eloquent. `#[Fillable]` silently drops the
non-column keys instead of erroring on an unknown column, so the hook can then
read them back off the same `$data` it was handed. `App\Models\Role` didn't
have one (it inherited Spatie's `$guarded = []`, so an unlisted key like
`permissions` would have hit the database as a column) — it now declares
`#[Fillable(['name', 'guard_name', 'is_system'])]` for exactly this reason.
Adding a new hook that reads a relation key off `$data` means checking the
target model has that key excluded from its `#[Fillable]` list.

## Scoping a repository to a subset of a table — use a model, not a base override
`BaseRepository` has no "forced constraint" hook, and adding one hits Eloquent
generics: `BaseRepository::baseQuery(): Builder<Model>` can't be overridden to
return `Builder<User>` (invariant `@template TModel`), and a `Builder<Model>`
can't call `->whereHas('roles')` (the base `Model` has no relations).

Instead, give the scope its own model: a thin subclass with `$table` pointing
back at the real table and a `whereHas(...)` **global scope** — see
`App\Models\Student` / `App\Models\Instructor` (`User` scoped to one role).
The repository just declares `$model` as that subclass, and every inherited
`all()` / `bulkUpdate()` / `bulkDelete()` picks the scope up through
`$this->model->newQuery()` — including the safety property that a bulk status
change can only touch rows of that role.

- A role/permission-style morph link is stored against the parent
  (`model_type = App\Models\User`), so the subclass must override
  `getMorphClass()` to return the parent class or `whereHas('roles')` matches
  nothing.
- Shared listing config (allowed filters/sorts, a search callback) goes on an
  abstract `Eloquent{X}RoleRepository` the concretes extend.
- This is for a *fixed* subset (a role, a flag). A *per-request* scope — "this
  user's enrollments" — is just a dedicated method that queries directly
  (`EloquentEnrollmentRepository::forStudent(int $id)`), not a model or a
  global scope.
- A *per-user CRUD* scope (an instructor managing only their own courses) is
  the model-scope pattern with a **dynamic** predicate:
  `App\Models\InstructorCourse` adds `where('instructor_id', Auth::id())` in its
  global scope, so `all()` / `bulkUpdate` / `bulkDelete` / route-model-binding
  are all constrained; the repo's `create()` stamps `instructor_id`. Set
  `protected $table` on the subclass — Eloquent won't guess it from the child
  class name.

## Validation lives in Form Requests, not the repository
A repository persists what it's given; it does not decide whether the input is
valid. Reserved names, an enum/catalogue check, uniqueness, "refuse to touch
this row" guards — all belong in the FormRequest (see `RoleRequest`,
`BulkDestroyRoleRequest`), not in the repository. A repository method that both
validates *and* persists (the project used to have
`EloquentRoleRepository::createCustomRole()` / `updateCustomRole()` /
`deleteCustomRole()` / `bulkDelete()` — all removed) should be split:
persistence stays on the inherited `create()`/`update()`/`delete()`/
`bulkDelete()`, validation moves to the form request.

- **One request class per resource, not one per verb.** `store` and `update`
  share a single `RoleRequest`. When the only difference is excluding the
  current row from a uniqueness check, use
  `Rule::unique(...)->ignore($this->route('role'))` — `ignore()` is a no-op
  when the route has no bound model (the `store` case), so the same rule set
  serves both without branching on the HTTP verb. Name it `{Resource}Request`
  (not `Store`/`Update{Resource}Request`).
- `Rule::exists(...)->where('bool_column', false)` fails on Postgres with
  `invalid input syntax for type boolean` — the rule stringifies its `where`
  clauses, and `(string) false === ''`. For a boolean condition inside a
  validation rule, use a closure rule that queries through Eloquent instead
  (the model's cast handles the boolean correctly across drivers); see
  `BulkDestroyRoleRequest`'s system-role check.
- Form Requests may keep the `@return array<string, mixed>` docblock on
  `rules()` and short inline `//` notes on a non-obvious rule — the
  no-comments rule is for `Eloquent*Repository` and controllers, not requests.
