---
paths:
  - 'resources/js/components/data-table/**'
---

# Data Table

## Two DataTable source hooks: useInertiaDataTable vs useHttpDataTable
`<DataTable>` takes any `DataTableSource`. Two implementations exist:
- `useInertiaDataTable` — data arrives as an Inertia prop; search/sort/page do `router.get(url, params, { only: [propKey] })` partial reloads. Use when the controller `index()` passes the rows as a prop.
- `useHttpDataTable` — data comes from a dedicated JSON endpoint via `useHttp`; search/sort/page live in the URL (`router.push`/`replace` client-side visits) and a `useEffect` keyed on the query string refetches. Use when the page action is `Inertia::render`-only and a sibling JSON action serves the rows.

Admin\RoleController uses the second style: `index()` renders only; `fetch()` (roles list) and `permissions()` (AdminPermission catalogue) return JSON, consumed by `useHttpDataTable` / `usePermissionCatalogue`. There is no `create`/`edit` route — both are modals (`RoleFormDialog`) on the index page; `store()`/`update()`/`destroy()` stay as plain redirect actions. Prefer this split for new admin resource pages. Page-specific components live in `resources/js/pages/admin/roles/components/`; shared TS types in `resources/js/types/role.ts`.

## Row selection: the `canSelect` prop
`<DataTable canSelect>` prepends the leading checkbox column itself — pages must not add a `{ id: 'select' }` column. `canSelect` is `boolean | ((row) => boolean)`: `true` selects every row, a predicate disables the checkbox on some rows (e.g. roles pass `(role) => !role.is_system`). Pair it with `renderSelectionActions` for the bulk-action toolbar. In the list/grid views the checkbox is overlaid on each card (cards should leave top-left room for it).

## View toggle: `renderCard` / `views` / `viewStorageKey`
Pass `renderCard={(row) => <SomeCard row={row} />}` to enable the table/list/grid toggle (hidden without it). `views` restricts the set (default all three); `viewStorageKey` remembers the choice in `localStorage` per viewer. List = full-width stacked cards, grid = `sm:grid-cols-2 lg:grid-cols-3`. View is presentation-only — it never touches `DataTableSource`.

## Filter card: the `filters` prop
`filters={[{ key, label, type: 'select'|'text', options? }]}` renders a **Filter** button + collapsible card. Each `key` MUST exist in the backing repository's `$allowedFilters` (Spatie QueryBuilder throws on unknown filter keys). The card holds a local draft; **Apply** commits the whole set through `DataTableSource.setFilters(next)` in one request (no query per keystroke/select). Applied values live in `filter[key]` URL params — same lifecycle as search/sort (shareable, back-button safe), page resets to 1. `DataTableSource.filters` is the *applied* set (both source hooks parse every `filter[*]` param except the search `filterKey`); the toolbar badge counts it. Roles exposes `is_system` as a "Type" (System/Custom) select.
