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
 * Swap the implementation (`useInertiaDataTable` today, a JSON-fetch hook later)
 * without touching the table component.
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
};
