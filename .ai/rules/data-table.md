---
paths:
  - 'resources/js/components/data-table/**'
---

# Data Table

## Two DataTable source hooks: useInertiaDataTable vs useHttpDataTable
`<DataTable>` takes any `DataTableSource`. Two implementations exist:
- `useInertiaDataTable` — data arrives as an Inertia prop; search/sort/page do `router.get(url, params, { only: [propKey] })` partial reloads. Use when the controller `index()` passes the rows as a prop.
- `useHttpDataTable` — data comes from a dedicated JSON endpoint via `useHttp`; search/sort/page live in the URL (`router.push`/`replace` client-side visits) and a `useEffect` keyed on the query string refetches. Use when the page action is `Inertia::render`-only and a sibling JSON action serves the rows.

**This is the pattern for admin resource lists.** The page action is
`Inertia::render`-only; a sibling `fetch()` returns the `Paginated<T>` JSON via
the shared `Controller::paginatedJson($paginator, $rows)` helper (base
`App\Http\Controllers\Controller`); row relations are always eager-loaded by
`BaseRepository::$with` (a repo property — no `?include=` / `$request->merge`
hack). Async filter options come from another sibling JSON action + a
fetch-on-mount hook.

- **Roles** (`Admin\RoleController`) — `fetch()` (roles) + `permissions()`
  (AdminPermission catalogue → `usePermissionCatalogue`). No `create`/`edit`
  route: both are `RoleFormDialog` modals on the index; `store`/`update`/
  `destroy` stay plain redirect actions. Components in
  `resources/js/pages/admin/roles/components/`, types in `resources/js/types/role.ts`.
- **Courses** (`Admin\CourseController`) — read-only. `fetch()` (courses, each
  row carrying `instructor` + `category` names) + `categories()`
  (`{id,name}` → `useCourseCategories`). `EloquentCourseRepository` sets
  `$with = ['category','instructor']`. Table + grid views only. Components in
  `resources/js/pages/admin/courses/components/`, types in `resources/js/types/course.ts`.

## Row selection: the `canSelect` prop
`<DataTable canSelect>` prepends the leading checkbox column itself — pages must not add a `{ id: 'select' }` column. `canSelect` is `boolean | ((row) => boolean)`: `true` selects every row, a predicate disables the checkbox on some rows (e.g. roles pass `(role) => !role.is_system`). Pair it with `renderSelectionActions` for the bulk-action toolbar. In the list/grid views the checkbox is overlaid on each card (cards should leave top-left room for it).

## View toggle: `renderCard` / `views` / `viewStorageKey`
Pass `renderCard={(row) => <SomeCard row={row} />}` to enable the table/list/grid toggle (hidden without it). `views` restricts the set (default all three); `viewStorageKey` remembers the choice in `localStorage` per viewer. List = full-width stacked cards, grid = `sm:grid-cols-2 lg:grid-cols-3`. View is presentation-only — it never touches `DataTableSource`.

## Filter card: the `filters` prop
`filters={[{ key, label, type: 'select'|'text', options? }]}` renders a **Filter** button + collapsible card. Each `key` MUST exist in the backing repository's `$allowedFilters` (Spatie QueryBuilder throws on unknown filter keys). The card holds a local draft; **Apply** commits the whole set through `DataTableSource.setFilters(next)` in one request (no query per keystroke/select). Applied values live in `filter[key]` URL params — same lifecycle as search/sort (shareable, back-button safe), page resets to 1. `DataTableSource.filters` is the *applied* set (both source hooks parse every `filter[*]` param except the search `filterKey`); the toolbar badge counts it. Roles exposes `is_system` as a "Type" select; courses exposes `status`, `is_free` (bool-cast via `AllowedFilter::callback` — Postgres rejects `where('bool_col','1')`), and `category_id` (options fetched via `useCourseCategories`).
