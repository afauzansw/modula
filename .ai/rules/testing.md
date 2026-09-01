---
paths:
  - 'tests/**'
  - 'database/seeders/**'
  - 'database/factories/**'
---

# Testing & seeders

## No model factories
`database/factories` has been deleted. **Do not create factories** or add
`HasFactory` to models, even though the Laravel Boost guidelines say to — the
`CLAUDE.md` "No model factories" note overrides that.

### In tests
Build rows with `Model::query()->create([...])`, or the plain global helpers in
`tests/Pest.php`: `createUser`, `createCourse`, `createCategory`, `createOrder`,
`createOtpCode`. Former factory *states* (`->unverified()`, `->paid()`,
`->expired()`, …) are just override arrays now — `createOrder(['status' =>
'failed'])`, `createUser(['email_verified_at' => null])`. Add a new `create*()`
helper when a model shows up in several test files.

`User`'s `email_verified_at` / `two_factor_*` are outside `#[Fillable]`, so they
need `forceCreate` / `forceFill` — `createUser()` already does this. A plain
`User::query()->create(['email_verified_at' => now()])` **silently drops** it.

Tests use `RefreshDatabase` (empty DB per test) and do not auto-seed. Tests that
need the three system roles seed `RolePermissionSeeder` themselves; otherwise
each test creates exactly the rows it asserts on.

### In seeders
Flat same-type batches → `Model::query()->insert([...])` with explicit
`created_at` / `updated_at`, `Hash::make()` for passwords, and `json_encode()`
for JSON columns (casts don't run on `insert()`). Rows whose id a child needs
immediately → `Model::query()->create([...])`. Resolve a related row an earlier
seeder created via query — the `Database\Seeders\ResolvesSeededRecords` trait
(`userByEmail()`, `randomUserIdWithRole()`). Every seeder guards on its own
table already having data (idempotent, safe to re-run / seed standalone).
