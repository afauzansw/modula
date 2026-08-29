# Modula

**Modula** is a private, non-commercial Learning Management System built as a portfolio project. It aims to cover the full lifecycle of an online course platform: instructors author courses (modules, lessons, quizzes, assignments), students browse a catalog and enroll (free or paid), work through lessons, take quizzes, submit assignments, rate courses, and earn certificates; admins manage users and categories. It is a single Laravel + Inertia application — **no separate REST/SPA API** — with Laravel 13 on the backend and React 19 + TypeScript on the frontend.

## Status

Early development. The repo is currently the [`laravel/react-starter-kit`](https://github.com/laravel/react-starter-kit) scaffold — auth, settings, passkeys, 2FA — plus this documentation. The LMS domain (roles, models, migrations, controllers, and the `/instructor` and `/admin` areas) is being built out. See [`docs/PROJECT_CONTEXT.md`](docs/PROJECT_CONTEXT.md) for the target domain design and the business rules behind it.

## Tech stack

**Backend**
- Laravel 13 (`laravel/framework ^13.17`), PHP 8.3+
- [Inertia.js 3](https://inertiajs.com) (`inertiajs/inertia-laravel ^3.0`) — server-driven pages, no API layer
- [Laravel Fortify](https://laravel.com/docs/fortify) — auth backend: login, registration, password reset, email verification, two-factor (TOTP), passkeys (WebAuthn)
- [Laravel Wayfinder](https://github.com/laravel/wayfinder) — typed TypeScript functions for routes/controllers
- [Laravel Boost](https://github.com/laravel/boost) (dev) — MCP server + AI guidelines/skills for use with Claude Code
- [Spatie laravel-permission](https://spatie.be/docs/laravel-permission) — role management (`admin` / `instructor` / `student`); `User` has the `HasRoles` trait
- _Planned:_ `phpoffice/phpword` (certificate `.docx` merge), Midtrans/Xendit SDK (sandbox payments)

**Frontend**
- React 19 + TypeScript 5.7
- Inertia React adapter (`@inertiajs/react ^3`); SSR via `@inertiajs/vite`
- Tailwind CSS v4 (`@tailwindcss/vite`)
- shadcn/ui + Radix UI primitives, `lucide-react` icons, `sonner` toasts
- Vite 8

**Tooling**
- [Bun](https://bun.sh) — JavaScript package manager and script runner (replaces npm)
- Pest 5 (tests, run against a PostgreSQL `my-lms_testing` database), Pint (PHP formatting), Larastan / PHPStan (static analysis), ESLint + Prettier
- Laravel Pail (log viewer), Laravel Sail (optional Docker)
- Queue, cache, and sessions all use the `database` driver

## Prerequisites

- PHP **8.3+** with the usual Laravel extensions, including `pdo_pgsql`
- Composer 2
- **[Bun](https://bun.sh)** — the JavaScript package manager and script runner for this project (replaces npm)
- **PostgreSQL 15+** running locally
- Node.js is not used directly, but Bun targets Node compatibility for Vite and related tooling

> This project runs **locally only** — it is not deployed anywhere. See [`docs/PROJECT_CONTEXT.md`](docs/PROJECT_CONTEXT.md#1-what-this-is).

## Getting started

```bash
git clone <repo-url> modula
cd modula

composer install
bun install
rm -f package-lock.json   # so `php artisan dev` and tooling detect Bun, not npm

cp .env.example .env
php artisan key:generate
```

### Database

Modula uses **PostgreSQL**. The databases are named `my-lms` / `my-lms_testing` (the project's former working title — kept to avoid a churn of infra renames). `.env.example` ships with `DB_CONNECTION=pgsql`; after copying it to `.env`, set your local credentials:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=my-lms
DB_USERNAME=postgres
DB_PASSWORD=
```

Create the databases, then migrate:

```bash
createdb my-lms           # or create it via a GUI / your Postgres tooling
createdb my-lms_testing   # separate database the test suite runs against
php artisan migrate
```

> **Tip:** if you change `.env` while a dev server is already running, fully stop and restart it. `php artisan serve` passes its environment to the PHP worker process and only hot-reloads on `APP_ENV` changes — a changed `DB_CONNECTION` won't take effect until restart.

## Running locally

```bash
composer dev
```

Runs the PHP server, queue worker, log tailer (Pail), and Vite together via `php artisan dev`. The app is served at **http://localhost:8000**.

Prefer separate terminals? Run the pieces individually:

```bash
php artisan serve
php artisan queue:listen --tries=1
bun run dev
```

## Common commands

| Command | Purpose |
| --- | --- |
| `composer test` | Pint check + PHPStan + full Pest suite |
| `vendor/bin/pest` | Run tests only (accepts a path or `--filter=`) |
| `vendor/bin/pint` | Auto-format PHP |
| `bun run lint` / `bun run types:check` | ESLint (autofix) / `tsc --noEmit` |
| `bun run build` | Production frontend build |
| `php artisan wayfinder:generate` | Regenerate typed route helpers (the Vite plugin also does this in dev) |

## Project structure

```
app/
  Actions/Fortify/      auth actions (create user, update password, ...)
  Http/
    Controllers/
    Middleware/          HandleInertiaRequests, HandleAppearance
    Requests/
  Models/                User  (LMS domain models to come)
  Providers/
bootstrap/app.php        middleware, routing, exception handling
config/
database/
  migrations/            starter-kit tables today; LMS schema being added
  factories/  seeders/
resources/
  js/
    pages/               Inertia page components (auth/, settings/, ...)
    layouts/             app/, auth/, settings/  (Student/Instructor/AdminLayout to come)
    components/ui/        shadcn/ui components
    actions/  routes/     Wayfinder-generated typed backend calls
    hooks/  lib/  types/
routes/
  web.php  settings.php  console.php
tests/                   Pest (Feature + Unit)
docs/
  PROJECT_CONTEXT.md     domain + business-logic reference
```

## Roles & areas

Three roles (`admin`, `instructor`, `student`), three route areas each with its own Inertia layout:

| Path | Audience | Focus |
| --- | --- | --- |
| `/` | students | catalog browsing, course consumption, quizzes, assignments, ratings |
| `/instructor` | instructors | course/module/lesson authoring, quiz builder, grading, progress |
| `/admin` | admins | user management, categories, oversight |

Rationale and the full domain model — enrollment/progress, the paid-checkout invariant, quizzes, ratings, certificates — are documented in [`docs/PROJECT_CONTEXT.md`](docs/PROJECT_CONTEXT.md).

## License

Private, non-commercial portfolio project. Not intended for redistribution.
