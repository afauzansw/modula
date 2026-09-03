---
paths:
  - 'resources/js/components/data-table/**'
  - 'resources/js/pages/admin/**'
  - 'app/Http/Controllers/Admin/**'
---

# Data Table

## Two DataTable source hooks: useInertiaDataTable vs useHttpDataTable
`<DataTable>` takes any `DataTableSource`. Two implementations exist:
- `useInertiaDataTable` — data arrives as an Inertia prop; search/sort/page do `router.get(url, params, { only: [propKey] })` partial reloads. Use when the controller `index()` passes the rows as a prop.
- `useHttpDataTable` — data comes from a dedicated JSON endpoint via `useHttp`; search/sort/page live in the URL (`router.push`/`replace` client-side visits) and a `useEffect` keyed on the query string refetches. Use when the page action is `Inertia::render`-only and a sibling JSON action serves the rows.

**This is the pattern for admin resource lists.** The page action is
`Inertia::render`-only; a sibling `fetch()` serves the rows as JSON.

`fetch()` returns `response()->json($this->repo->all(...))` **directly** — the
raw paginator. `useHttpDataTable` reads `data` / `last_page` / `total` /
`per_page` straight off Laravel's paginator JSON, so no wrapper is needed and
the rows are the models' own `toArray()` (`RoleController`, `CategoryController`).
Only reach for the `foreach ($paginator->items() as $x) { ... }` +
`$this->paginatedJson($paginator, $rows)` transform when a row needs a
*flattened relation* or a *computed field* that isn't a plain column —
`CourseController` flattens `instructor` / `category` to names and adds a
`thumbnail` media URL.

Row relations are eager-loaded by `BaseRepository::$with` (a repo property —
no `?include=` / `$request->merge` hack; request-driven includes were removed).
Async filter options come from another sibling JSON action + a fetch-on-mount hook.

For create/edit, the default is ONE `{Resource}FormDialog` modal (create
when no row passed, edit when one is) on the index — no `create`/`edit`
routes (`Route::resource(...)->except(['show', 'create', 'edit'])`) — plus
`store`/`update`/`destroy`/`bulkDestroy` as plain redirect actions.
`{Resource}Actions` holds the per-row Edit + Delete controls (a
`<ConfirmDialog>` for delete); bulk delete is `<DataTable canSelect>` +
`renderSelectionActions` wiring a `<ConfirmDialog form={...bulkDestroy.form()}
fields={{ ids }}>`. Bulk *status* changes are the same shape — a
`<ConfirmDialog>` per action carrying `fields={{ ids, <col>: <value> }}` to a
`PATCH .../status` route (`bulkUpdate($ids, [<col> => <value>])`).

A bigger form goes on its **own page** instead (`create.tsx` / `edit.tsx` +
a shared `{Resource}Form` component, `Route::resource(...)->except(['show'])`,
the "New" button and per-row Edit are `<Link>`s). Instructor courses do this.
Native `<select>` / `<textarea>` (styled to match `<Input>`) submit reliably
in an Inertia `<Form>`; the shadcn `Select` does not. A file input +
`BaseRepository::$fileKeys` handles thumbnails.

- **Roles** (`Admin\RoleController`) — `fetch()` (roles) + `permissions()`
  (AdminPermission catalogue → `usePermissionCatalogue`). No `create`/`edit`
  route: both are `RoleFormDialog` modals on the index; `store`/`update`/
  `destroy` stay plain redirect actions. Components in
  `resources/js/pages/admin/roles/components/`, types in `resources/js/types/role.ts`.
- **Admins** (`Admin\AdminUserController`) — full CRUD. `fetch()` (transform:
  name / email / direct-permission names). `AdminFormDialog` (name / email /
  password — optional on edit — + the shared `<PermissionCheckboxList>` from
  `@/components`, fed by `usePermissionCatalogue`). Search box = `name`; filter
  card = **Email** (text) + **Permission** (select, catalogue from the same
  hook). `canSelect` + bulk delete; the controller skips the current user's own
  id on `destroy` / `bulkDestroy`. Scoped by the **`Admin` model** (global
  scope: has ≥1 direct `AdminPermission`; `getGuardNames()` borrows `User`'s
  since `Admin` isn't an auth model). `EloquentAdminRepository::create()` /
  `update()` `forceCreate` the verified user + `syncPermissions`. Types in
  `resources/js/types/admin-user.ts`.
- **Courses** (`Admin\CourseController`) — read-only. `fetch()` (courses, each
  row carrying `instructor` + `category` names + a `thumbnail` media URL) +
  `categories()` (`{id,name}` → `useCourseCategories`). `EloquentCourseRepository`
  sets `$with = ['category','instructor','media']`. Table + grid views only.
  Components in `resources/js/pages/admin/courses/components/`, types in
  `resources/js/types/course.ts`.
- **Course Category** (`Admin\CategoryController`) — full CRUD. `fetch()`
  returns the raw paginator (rows are the `Category` model itself — `name`,
  `slug`). `CategoryFormDialog` has a name field only; the slug is derived
  server-side in `CategoryRequest::prepareForValidation`. Search + bulk delete,
  **no filter card, table view only** (no `renderCard` / `filters`).
  `canSelect` with no predicate — every category is deletable. Components in
  `resources/js/pages/admin/categories/components/`, types in
  `resources/js/types/category.ts`.
- **Student Payment** (`Admin\PaymentController`) — read-only, `fetch()` only.
  Rows are `Payment`s with the student / course / order flattened off the
  `order.user` / `order.course` chain (`EloquentPaymentRepository` sets
  `$with = ['order.user', 'order.course']`); search hits `order.user.name` via
  `AllowedFilter::partial('student', 'order.user.name')`, sortable on `amount`
  / `paid_at`. **Table view only, no filter card, no selection.** Formatters in
  `resources/js/pages/admin/payments/lib/format.ts`, types in
  `resources/js/types/payment.ts`.
- **Certificate** (`Admin\CertificateController`) — read-only, `fetch()` only
  (transform: student / course names + `certificate_number` + `issued_at`,
  `EloquentCertificateRepository` sets `$with = ['user', 'course']`). Search box
  = partial `certificate_number`; **filter card = Student + Course selects**
  (`user_id` / `course_id` exact). Their options come from the **existing**
  `StudentController::fetch` / `CourseController::fetch` listings (first page,
  mapped to `{id, name}` in `useCertificateStudents` / `useCertificateCourses`)
  — no dedicated options endpoint. Table view only, sortable on `issued_at`.
  Types in `resources/js/types/certificate.ts`.
- **Instructor Courses** (`Instructor\CourseController`) — full CRUD, scoped to
  the signed-in instructor by the **`InstructorCourse` model** (`$table =
  'courses'`, `getMorphClass() → Course`, a *dynamic* global scope
  `where('instructor_id', Auth::id())` — so it also constrains `bulkUpdate` /
  `bulkDelete` / route-model-binding). `EloquentInstructorCourseRepository::create()`
  stamps `instructor_id`. Create/edit are **pages** (`create.tsx` / `edit.tsx`
  + `CourseForm`), not modals. Search = `title`; filter card = Category
  (select) + Price (Free/Paid → `is_free`) + Status (select). Bulk **Publish /
  Unpublish / Delete**. `CourseRequest` derives the slug from the title
  (globally unique) and forces `price = 0` when `is_free`. Types in
  `resources/js/types/instructor.ts`.
- **Student / Instructor** (`Admin\StudentController`, `Admin\InstructorController`)
  — one shared `<UserDirectoryTable>` component (name / email / status / joined,
  searchable, table only). Each controller injects `Student{,Instructor}RepositoryInterface`;
  the repos share `EloquentUserRoleRepository` (name/email `search` callback
  filter) and are role-scoped by the **`Student` / `Instructor` models** — thin
  `User` subclasses (`$table = 'users'`, `getMorphClass() → User::class`) with a
  `whereHas('roles', …)` global scope. That scope also constrains
  `bulkUpdate()`, so `bulkUpdateStatus` (block / unblock via `is_blocked`,
  `PATCH .../status`) can only touch users of that role. `fetch()` returns the
  raw paginator; types in `resources/js/types/user.ts`.

## Row selection: the `canSelect` prop
`<DataTable canSelect>` prepends the leading checkbox column itself — pages must not add a `{ id: 'select' }` column. `canSelect` is `boolean | ((row) => boolean)`: `true` selects every row, a predicate disables the checkbox on some rows (e.g. roles pass `(role) => !role.is_system`). Pair it with `renderSelectionActions` for the bulk-action toolbar. In the list/grid views the checkbox is overlaid on each card (cards should leave top-left room for it).

## View toggle: `renderCard` / `views` / `viewStorageKey`
Pass `renderCard={(row) => <SomeCard row={row} />}` to enable the table/list/grid toggle (hidden without it). `views` restricts the set (default all three); `viewStorageKey` remembers the choice in `localStorage` per viewer. List = full-width stacked cards, grid = `sm:grid-cols-2 lg:grid-cols-3`. View is presentation-only — it never touches `DataTableSource`.

## Filter card: the `filters` prop
`filters={[{ key, label, type: 'select'|'text', options? }]}` renders a **Filter** button + collapsible card. Each `key` MUST exist in the backing repository's `$allowedFilters` (Spatie QueryBuilder throws on unknown filter keys). The card holds a local draft; **Apply** commits the whole set through `DataTableSource.setFilters(next)` in one request (no query per keystroke/select). Applied values live in `filter[key]` URL params — same lifecycle as search/sort (shareable, back-button safe), page resets to 1. `DataTableSource.filters` is the *applied* set (both source hooks parse every `filter[*]` param except the search `filterKey`); the toolbar badge counts it. Roles exposes `is_system` as a "Type" select; courses exposes `status`, `is_free` (bool-cast via `AllowedFilter::callback` — Postgres rejects `where('bool_col','1')`), and `category_id` (options fetched via `useCourseCategories`).
