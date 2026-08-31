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
`<DataTable canSelect>` prepends the leading checkbox column itself — pages must not add a `{ id: 'select' }` column. `canSelect` is `boolean | ((row) => boolean)`: `true` selects every row, a predicate disables the checkbox on some rows (e.g. roles pass `(role) => !role.is_system`). Pair it with `renderSelectionActions` for the bulk-action toolbar.
