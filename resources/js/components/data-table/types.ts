import type {
    OnChangeFn,
    PaginationState,
    SortingState,
} from '@tanstack/react-table';

/**
 * The contract a `<DataTable>` needs from whatever is feeding it rows. Pagination,
 * sorting and filtering are all "manual" (server-computed) — the source owns the
 * actual fetching and only hands back the current page plus the state/among the
 * setters TanStack Table needs to drive it.
 *
 * Swap the implementation (`useInertiaDataTable` or `useHttpDataTable`) without
 * touching the table component.
 */
export type DataTableSource<TData> = {
    /** The current page of rows. */
    data: TData[];
    /** Total number of pages for the active filter set. */
    pageCount: number;
    /** Total number of rows across every page for the active filter set. */
    rowCount: number;
    /** A reload is in flight. */
    isLoading: boolean;

    sorting: SortingState;
    onSortingChange: OnChangeFn<SortingState>;

    pagination: PaginationState;
    onPaginationChange: OnChangeFn<PaginationState>;

    /** Current search term (immediate — reflects keystrokes before the reload). */
    globalFilter: string;
    onGlobalFilterChange: (value: string) => void;

    /** Currently applied non-search filter values, keyed by `filter[key]`. */
    filters: Record<string, string>;
    /**
     * Replace the whole non-search filter set in one request (the filter card
     * batches its changes behind an Apply button). Empty-string values are
     * dropped; resets to the first page.
     */
    setFilters: (next: Record<string, string>) => void;
};

/** The layouts `<DataTable>` can render its rows in. */
export type DataTableView = 'table' | 'list' | 'grid';

/**
 * One control in the `<DataTable>` filter card. `key` must be an allowed filter
 * on the backing repository (`$allowedFilters`).
 */
export type DataTableFilterDef = { key: string; label: string } & (
    | { type: 'select'; options: { label: string; value: string }[] }
    | { type: 'text'; placeholder?: string }
);
