# Project Context

Domain and business-logic reference for **Modula**, a personal learning-management-system project. Read this before starting a task so you understand *why* the system is shaped the way it is — not just what the stack is.

Coding conventions (Laravel / Inertia / testing patterns, file structure, naming) are governed by `CLAUDE.md` and the Laravel Boost guidelines and skills. **This document is only about the domain.**

---

## 1. What this is

- **Modula** — a **Learning Management System**, an online course platform.
- **Private and non-commercial.** A personal portfolio project by Fauzan (senior Laravel developer moving toward fullstack / frontend). It has no customers and is not a product.
- **Not deployed — runs locally only.** There is no staging or production environment, no live URL, and no deploy pipeline. The project runs on the developer's machine, for learning and portfolio purposes.
- **Consequence for decisions:** favor clean architecture and modern, demonstrable patterns over production hardening, horizontal scale, or monetization. **Local-first is an acceptable simplification throughout the project:**
  - No production-grade infrastructure concerns — CDN, horizontal scaling, and zero-downtime deploys are all out of scope.
  - The CI workflow (`.github/workflows/tests.yml`, job `tests`) runs **checks only** — lint, static analysis, and the test suite. It does not deploy anywhere and should stay that way unless explicitly revisited.
  - Payments are deliberately sandbox-only (§6), consistent with both this and the non-commercial scope.

## 2. Implementation status

> This section dates fastest. Verify against the repo before trusting it.

As of the last update to this document:

- The repo is the `laravel/react-starter-kit` scaffold + this documentation.
- **Built:** authentication via Laravel Fortify (login, registration, password reset, email verification, 2FA/TOTP, passkeys); settings pages; Spatie `laravel-permission` (installed, `User` has `HasRoles`); the **16 domain migrations** (§10) applied to `my-lms`; an **Eloquent model + factory for every domain table**, with relationships and casts wired; and a `DemoContentSeeder` (run by `DatabaseSeeder`) that builds a sample course graph and the three roles.
- **Built:** the layered architecture's foundation — `BaseRepository` + `AuthRepository` (Phase 1, see [Architecture](#architecture)) with the `otp_codes` table for the OTP password-change flow.
- **Not yet built:** a non-demo role/permission seeder, the `/instructor` and `/admin` areas, controllers / form requests / policies, the domain repositories and the whole Services layer (Architecture → "Planned"), and all feature behaviour (checkout, quiz grading, the `progress_percent` rollup, certificate generation, the rating eligibility/edit/snapshot rules). Models and schema exist; the application logic does not.
- Treat sections 3–11 as the **target design for behaviour**, even though the tables and models now exist.

## Environment and tooling

_(Unnumbered so the existing section anchors — e.g. [Open decisions](#12-open-decisions) — stay stable.)_

- **Database: PostgreSQL** (not MySQL). Earlier drafts of this doc assumed MySQL; §6 and §11 have been corrected.
  - `config/database.php`'s default connection and `DB_CONNECTION` in `.env` / `.env.example` are `pgsql`.
  - The test suite also runs on PostgreSQL — `phpunit.xml` points at a `my-lms_testing` database, not SQLite.
  - The app talks to a local PostgreSQL 17.x instance on `127.0.0.1:5432`. How a fresh setup should provision Postgres (native install / Docker / Herd / Laragon) is not pinned — see [Open decisions](#12-open-decisions).
- **JavaScript package manager & runtime: Bun** (not npm).
  - Use `bun install`, `bun run dev`, `bun run build`.
  - Node.js may still matter as a *compatibility target* for Vite and related tooling, but dependency installation and script execution go through Bun.
  - `php artisan dev` (what `composer dev` runs) auto-detects the package manager from the lock file — `bun.lockb` → Bun, `package-lock.json` → npm. So once `bun install` has run, **delete `package-lock.json`** or it will keep choosing npm.
  - **Follow-ups (not done in this pass):**
    - `.github/workflows/tests.yml` still has a "Setup Node" step and no Bun setup. It needs `oven-sh/setup-bun` — replacing the Node step, or kept alongside it if something specific still requires Node.
    - `composer.json` scripts `setup` and `ci:check` hard-code `npm install` / `npm run …`; switch them to `bun`.
    - Remove `package-lock.json` after the first `bun install` (see above).
- **No deployment.** See §1. CI is checks-only; there is no deploy target and no production environment to design for.

## Architecture

Modula uses a layered architecture. **Controllers stay thin** — no Eloquent
queries (`Model::query()`, `Model::where(...)`, `->with(...)`, …) and no
third-party SDK calls inside a controller. A controller validates input,
delegates to a repository or a service, and returns a response. Two layers hold
the logic controllers must not:

| Layer | Path | Owns | Rule of thumb |
| --- | --- | --- | --- |
| **Repositories** | `app/Repositories/` | All data access for the app's own models — Eloquent queries, filtering, joins, aggregations. | *"Give me data about X."* |
| **Services** | `app/Services/` | Integration with external systems and multi-step business processes (payment gateway calls, certificate `.docx` merge, notifications, orchestration). A service may use one or more repositories internally. | *"Talk to an external system"* / *"perform a multi-step action."* |

Interfaces live in `Contracts/`, Eloquent implementations in `Eloquent/`.
`RepositoryServiceProvider` (registered in `bootstrap/providers.php`) binds each
interface to its concrete class via the `$bindings` array; controllers and
services constructor-inject the **interface**, never the implementation.

### `BaseRepository` (`app/Repositories/Eloquent/BaseRepository.php`)

`abstract`, implements `BaseRepositoryInterface`. Every Eloquent repository
extends it and gets, for free:

- **`all(array $filters = [], int $perPage = 15)`** — a listing built through
  `Spatie\QueryBuilder`. Each concrete repository declares protected
  `$allowedFilters` / `$allowedSorts` / `$allowedIncludes`, and every listing
  endpoint then supports query-string filtering/sorting/pagination out of the
  box, e.g. `?filter[status]=published&sort=-created_at&page=2&include=modules`.
  The `$filters` argument adds forced constraints on top (equality, or `whereIn`
  for array values).
- **`find` / `findOrFail` / `create`**.
- **`update(Model, array)`** and **`updateWhere(array $conditions, array $data)`**
  — both wrapped in `DB::transaction`. `updateWhere` conditions accept
  `['col' => $val]` (equality), `['col' => [$a, $b]]` (`whereIn`), and
  `[fn (Builder $q) => …]` (arbitrary), mixed freely; it returns the affected
  row count.
- **`bulkUpdate(array $ids, array $data)`** and **`bulkDelete(array $ids)`** —
  one query each, wrapped in `DB::transaction`, returning the affected row count.
  `bulkUpdate` is the intended path for batch status changes.

`BaseRepository` / `BaseRepositoryInterface` are **never bound directly** — only
concrete subclasses are.

### `AuthRepository` — the proving ground + the OTP password-change flow

`AuthRepositoryInterface` → `EloquentAuthRepository` (extends `BaseRepository`).
Standalone (it does not extend `BaseRepositoryInterface`) — a thin, data-oriented
wrapper over Laravel's auth state, not CRUD on a domain entity:

- `login()` / `register()` wrap `Auth::attempt()` / `Hash::make()` (the `User`
  model's `hashed` cast is idempotent, so hashing here does not double-hash).
- `profile(int $userId)`.
- **OTP-gated password change:** `sendPasswordChangeOtp()` writes a row to the
  `otp_codes` table (`user_id` FK cascade, `code`, `expires_at`, `used_at`,
  indexed on `(user_id, code)`) and dispatches
  `PasswordChangeOtpNotification`. **This is the one place a repository triggers
  an email** — and it still goes through a Notification, not an inline
  `Mail::send()`, so templating and transport stay swappable.
  `verifyOtpAndChangePassword()` checks the code matches, is unexpired and
  unused (all inside `DB::transaction` with `lockForUpdate`), sets the new
  hashed password, and stamps `used_at` so the code cannot be replayed. A
  wrong / expired / used code returns `false` and changes nothing.

`AuthRepository` is **fully implemented and tested** (`tests/Feature/Repositories/`)
— it is the reference every Phase 2 repository copies.

### Planned, not yet implemented

Phase 2 is designed but not built. It follows the exact `EloquentAuthRepository`
pattern (extend `BaseRepository`, declare `$model` + allowed filters/sorts, add
entity-specific methods):

- **Domain repositories** for every §10 table — `Course`, `Module`, `Lesson`,
  `Enrollment`, `LessonProgress`, `Order`, `Quiz`, `QuizAttempt`, `Assignment`,
  `Submission`, `Rating`, `Certificate` — each `*RepositoryInterface` *extends*
  `BaseRepositoryInterface` and adds only entity-specific queries (e.g.
  `OrderRepositoryInterface::findActiveOrderForCourse`,
  `LessonProgressRepositoryInterface::markCompleted`).
- **Services layer** (`app/Services/`):
  - `Payment/` — `PaymentGatewayInterface` (`charge`, `verifyWebhookSignature`,
    `parseWebhookPayload`) so Midtrans/Xendit is swappable without touching
    `OrderService` or controllers; `MidtransPaymentService`; `OrderService`
    (create order, enforce the one-active-order rule of §6, react to `OrderPaid`).
  - `Certificate/CertificateGenerationService` — merges the course's `.docx`
    template and issues the PDF + `Certificate` record (§9).
  - `Enrollment/EnrollmentService` — free-course direct enroll, or enroll on
    `OrderPaid` (§6).
  - `Progress/ProgressCalculationService` — recomputes `Enrollment.progress_percent`
    from `LessonProgress` rows (§5).

## 3. Roles and application areas

Three roles: `admin`, `instructor`, `student`, managed with Spatie `laravel-permission` — `User` uses the `HasRoles` trait, and there is **no `role` column** on `users`.

| Area | Path | Default for | Layout | Purpose |
| --- | --- | --- | --- | --- |
| Student | `/` | students (authenticated landing) | `StudentLayout` | Browse catalog, consume courses, take quizzes, submit assignments, rate courses |
| Instructor | `/instructor` | instructors | `InstructorLayout` | Author courses / modules / lessons, build quizzes & questions, grade submissions, view student progress |
| Admin | `/admin` | admins | `AdminLayout` | User management, categories, platform-wide oversight |

**Why three separate Inertia layouts** instead of one shared layout with role conditionals: instructor and admin surfaces are authoring-heavy (forms, tables, builders); the student surface is consumption-heavy (reading, video, progress tracking). The UX diverges enough that separate layouts are cleaner than branching one.

**Post-login redirect** is role-dependent (student → `/`, instructor → `/instructor`, admin → `/admin`). The precise rule depends on the dual-role decision — see [Open decisions](#12-open-decisions).

## 4. Course structure

`Course` → has many `Module` → has many `Lesson`.

- A `Lesson` has a `type`: `video`, `text`, `quiz`, or `assignment`.
- A `quiz` lesson carries a `Quiz`; an `assignment` lesson carries an `Assignment` (§7).
- Courses belong to a `Category` (admin-managed).

## 5. Enrollment and progress

- **`Enrollment`** links a student (`User`) to a `Course`. Tracks `status` (`active` | `completed`) and `progress_percent`.
- **`LessonProgress`** — one row per `user` + `lesson`, recording `completed_at`. This is the **source of truth** for what a student has finished.
- **`Enrollment.progress_percent` is derived**: completed lessons ÷ total lessons in the course. It is a cached rollup of `LessonProgress`, not independently authoritative — **recompute it whenever lesson progress changes**.
- There is **no un-enrollment** in this application. Nothing needs to handle "the student left the course." (This deliberately simplifies ratings — §8 — and certificates — §9.)
- Course completion (all lessons done → `Enrollment.status = completed`) triggers certificate generation (§9).

## 6. Payments and checkout

### Free vs paid

- `Course.is_free` / `Course.price`.
- **Free courses skip payment entirely** — straight to `Enrollment`.
- **Paid courses** go through `Order` → `Payment`.

### The one-active-order rule (core invariant)

A user may hold **only one active order per course at a time**, where *active* = status `pending` **or** `paid`.

- Attempting checkout again while an order is `pending` or `paid` is **blocked**.
- Only `failed` or `expired` orders free the user to start a new checkout attempt.

**Enforced at the database level, not only in app code** — a **partial unique index** on `orders`:

```sql
CREATE UNIQUE INDEX orders_one_active_per_course
ON orders (user_id, course_id)
WHERE status IN ('pending', 'paid');
```

PostgreSQL supports partial (filtered) unique indexes natively, so "at most one active order per `(user, course)`" is a single index with no helper columns. `failed` / `expired` orders fall outside the `WHERE` clause and never block a new checkout. App-layer checks still exist for UX, but this index is the race-condition backstop.

The migration (`database/migrations/*_create_orders_table.php`) creates exactly this index with `DB::statement()` — the fluent schema builder has no partial-index method. (An earlier draft used a MySQL-style workaround, a stored `active_lock` column plus a three-column unique constraint; it has been replaced.)

### Enrollment on payment — keep it decoupled

- A **verified gateway webhook** fires an **`OrderPaid` event**.
- An **event listener** creates the `Enrollment` — **not** inline checkout-controller logic.
- **Why:** keeps the payment → enrollment path testable and lets other reactions (notifications, certificate eligibility, analytics) subscribe to the same event.
- **Local-only caveat:** there is no public URL for a gateway to call (§1). Sandbox webhook testing needs a tunnel (`ngrok`, `expose`, …) or the sandbox gateway's manual "simulate callback" tooling. The verified-webhook design still stands; only the delivery path is affected.

### Gateway

- Target gateways are Indonesian: **Midtrans** or **Xendit**, **sandbox / test mode only**.
- Real payment processing is **out of scope** given the non-commercial nature — revisit only if explicitly decided ([Open decisions](#12-open-decisions)).

### UI status badges (course list / "My Courses")

| Order state | Badge | Action offered |
| --- | --- | --- |
| `paid` | **Enrolled** | — |
| `pending` | **Payment Pending** (ideally with an expiry countdown) | continue payment |
| `failed` | **Payment Failed** | retry checkout |
| `expired` | **Expired** | retry checkout |

## 7. Quizzes and assignments

### Quizzes

- `Quiz` belongs to a `Lesson` of type `quiz`.
- `Quiz` has many `Question`; each `Question` has many `Option` (multiple choice) and a `passing_score`.
- `QuizAttempt` records, per attempt: the user's `score`, pass/fail, and raw `answers` (JSON).
- Multiple attempts are allowed (the model records attempts, plural).

### Assignments

- `Assignment` belongs to a `Lesson` of type `assignment`.
- A student creates a `Submission` (file upload).
- An instructor grades **manually** — sets `grade` + `feedback`. No auto-grading.

## 8. Ratings

One `Rating` per user per course: `stars` (1–5) + optional `review_text`.

### Eligibility

- A student may rate a course only once `Enrollment.progress_percent >= 30`.

### Edit cap

- A rating may be edited **at most 2 times** (`edit_count`, enforced in app code). The 3rd edit attempt is rejected.

### Progress snapshot (non-obvious — do not "fix" this)

At **first submission**, two fields are frozen and **never updated on edit**:

- `progress_percent_at_review` — the student's course progress at the moment they first reviewed.
- `last_lesson_id_at_review` — the last lesson they had reached at that moment.

**Why:** the public review list shows context such as *"Reviewed at Module 3: State Management (45%) · edited"*. If these values tracked live progress, that context would drift after the fact and stop reflecting what the reviewer actually experienced when they wrote the review. Freezing them keeps the signal honest. The student's *actual* progress keeps changing independently — that is expected and fine.

### Public display

- Show an **"edited" badge** when `edit_count > 0`.
- Because there is no un-enrollment (§5), the rating lifecycle never has to handle a reviewer who left the course.

## 9. Certificates

- Each `Course` may have a `certificate_template_path` — an uploaded **`.docx`** file containing merge-field placeholders (student name, course title, date, certificate number, …).
- On course completion, a `Certificate` row is generated per `user` + `course`:
  1. Merge the `.docx` template (planned: `phpoffice/phpword` `TemplateProcessor`).
  2. Render the result to **PDF**, stored at `Certificate.file_path`.
- **Placeholder syntax is not finalized.** It must be fixed and documented before the generation service is built, and surfaced in the instructor upload UI so non-technical authors know which fields are available. Candidate syntax: `${student_name}`, `${course_name}`, `${date}`, `${cert_number}`. See [Open decisions](#12-open-decisions).

## 10. Database schema

Migrations live in `database/migrations/` and have been applied to `my-lms` (see §2).

**Domain tables (16):** `categories`, `courses`, `modules`, `lessons`, `enrollments`, `lesson_progress`, `orders`, `payments`, `quizzes`, `questions`, `options`, `quiz_attempts`, `assignments`, `submissions`, `ratings`, `certificates`.

**Roles / permissions:** the five Spatie `laravel-permission` tables (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`). **There is no `role` column on `users`** — role checks go through the `HasRoles` trait (`$user->hasRole('instructor')`, `$user->assignRole('student')`, …).

**Auth infrastructure:** `otp_codes` (`user_id` FK cascade, `code`, `expires_at`, `used_at`, indexed on `(user_id, code)`) backs the OTP password-change flow — see [Architecture](#architecture).

## 11. Architectural decisions & rationale (quick reference)

| Decision | Why |
| --- | --- |
| Separate `StudentLayout` / `InstructorLayout` / `AdminLayout` | Consumption vs authoring UX differ too much for one conditional layout |
| `Enrollment` created by an `OrderPaid` **event listener**, not the checkout controller | Testable, decoupled; other side effects attach to the same event |
| One-active-order enforced by a **partial unique index** (`orders (user_id, course_id) WHERE status IN ('pending','paid')`), not just validation | Race-safe; PostgreSQL supports partial unique indexes natively (§6) |
| `Enrollment.progress_percent` is a **derived rollup** of `LessonProgress` | `LessonProgress` is the single source of truth; the percent is a cache |
| Rating `*_at_review` fields **frozen at first submission** | Public review context must not drift after the review was written |
| Sandbox-only payment gateway | Non-commercial project; real-money processing is out of scope |
| No un-enrollment | Keeps enrollment / rating / certificate lifecycles simple by design |

## 12. Open decisions

Flag these if a task touches them. **Do not silently pick one.**

1. **Dual roles** — can one user hold `student` and `instructor` simultaneously? Affects post-login redirect logic and whether a role-switcher UI is needed.
2. **Certificate placeholder syntax** — the exact merge-field tokens and the instructor-facing documentation for them must be fixed before the certificate generator is built.
3. **Payment gateway scope** — sandbox / test-mode only (Midtrans / Xendit), consistent with the no-deployment and non-commercial scope (§1). Real integration only if explicitly revisited.
4. **Quiz `passing_score` placement** — the schema puts `passing_score` on `quizzes` (quiz-level, default 70); §7's prose still describes it as question-level and should be reconciled.
5. **Local PostgreSQL provisioning** — the app talks to a local Postgres 17.x on `127.0.0.1:5432`, but there is no agreed setup for fresh clones (native install vs Docker vs Herd vs Laragon). Pick one and document it in the README.
6. **Database rename** — the databases are still `my-lms` / `my-lms_testing` from the old working title. Renaming them to `modula` is deferred (touches `.env`, `.env.example`, `phpunit.xml`, and needs the databases recreated); do it only if the mismatch becomes annoying.

## 13. Conventions

- **Commits:** Conventional Commits (`feat:`, `fix:`, `chore:`, …), kept terse — e.g. `chore: bootstrap` for the initial commit.
- Code-level conventions live in `CLAUDE.md` and the Laravel Boost guidelines / skills, not here.
