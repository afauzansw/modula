import { router, usePage } from '@inertiajs/react';
import type {
    OnChangeFn,
    PaginationState,
    SortingState,
} from '@tanstack/react-table';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { Paginated } from '@/types';
import type { DataTableSource } from './types';

type UseInertiaDataTableOptions = {
    /** Inertia prop key holding the `Paginated<T>` payload. */
    propKey: string;
    /** Base path the reloads target — usually `someRoute.index().url`. */
    url: string;
    /**
     * `filter[...]` key the search box writes to. Must be listed in the
     * repository's `$allowedFilters` (partial match by default).
     */
    filterKey: string;
    /**
     * Maps a TanStack column id to the backend `sort` field. Columns absent from
     * the map sort by their own id. Pass a stable reference (module scope or
     * memoized) — it feeds `useMemo` dependency arrays.
     */
    sortFields?: Record<string, string>;
    /** Debounce (ms) applied to search-driven reloads. */
    debounceMs?: number;
};

/**
 * Kept loose (index signature) so it satisfies Inertia's `RequestPayload` without
 * a cast. Only `filter`, `sort` and `page` are ever set.
 */
type QueryParams = Record<
    string,
    string | number | Record<string, string> | undefined
>;

type VisitOptions = {
    debounce?: boolean;
    replace?: boolean;
};

function queryString(url: string): URLSearchParams {
    return new URLSearchParams(
        url.includes('?') ? url.slice(url.indexOf('?') + 1) : '',
    );
}

/**
 * `DataTableSource` implementation backed by an Inertia partial reload. Search,
 * sort and page live in the URL query string (shareable, back-button safe); each
 * change issues a `router.get(url, params, { only: [propKey] })` so only the
 * table's prop is recomputed server-side by the existing `BaseRepository::all()`
 * query builder.
 */
export function useInertiaDataTable<TData>({
    propKey,
    url,
    filterKey,
    sortFields = {},
    debounceMs = 300,
}: UseInertiaDataTableOptions): DataTableSource<TData> {
    const page = usePage<Record<string, unknown>>();
    const paginated = page.props[propKey] as unknown as Paginated<TData>;

    const urlSearch = useMemo(
        () => queryString(page.url).get(`filter[${filterKey}]`) ?? '',
        [page.url, filterKey],
    );

    const sortParam = useMemo(
        () => queryString(page.url).get('sort') ?? '',
        [page.url],
    );

    // Every `filter[*]` param except the search key drives the filter card.
    const filters = useMemo(() => {
        const out: Record<string, string> = {};

        for (const [key, value] of queryString(page.url).entries()) {
            const match = key.match(/^filter\[(.+)\]$/);

            if (match && match[1] !== filterKey) {
                out[match[1]] = value;
            }
        }

        return out;
    }, [page.url, filterKey]);

    const fieldToColumn = useMemo(() => {
        const inverse: Record<string, string> = {};

        for (const [columnId, field] of Object.entries(sortFields)) {
            inverse[field] = columnId;
        }

        return inverse;
    }, [sortFields]);

    const sorting: SortingState = useMemo(() => {
        if (sortParam === '') {
            return [];
        }

        const desc = sortParam.startsWith('-');
        const field = desc ? sortParam.slice(1) : sortParam;

        return [{ id: fieldToColumn[field] ?? field, desc }];
    }, [sortParam, fieldToColumn]);

    const pagination: PaginationState = {
        pageIndex: Math.max(0, (paginated.current_page ?? 1) - 1),
        pageSize: paginated.per_page ?? 10,
    };

    // The search box shows `override` while a debounced reload is pending, then
    // falls back to the URL. Clearing the override once the URL catches up (or
    // diverges via history navigation) keeps the two in sync without an effect.
    const [override, setOverride] = useState<string | null>(null);
    const searchValue = override ?? urlSearch;

    if (override !== null && override === urlSearch) {
        setOverride(null);
    }

    const [isLoading, setIsLoading] = useState(false);
    const debounceTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const clearDebounce = useCallback(() => {
        if (debounceTimer.current) {
            clearTimeout(debounceTimer.current);
            debounceTimer.current = null;
        }
    }, []);

    useEffect(() => clearDebounce, [clearDebounce]);

    const currentParams = useCallback((): QueryParams => {
        const params: QueryParams = {};

        const filter: Record<string, string> = { ...filters };

        if (searchValue !== '') {
            filter[filterKey] = searchValue;
        }

        if (Object.keys(filter).length > 0) {
            params.filter = filter;
        }

        if (sortParam !== '') {
            params.sort = sortParam;
        }

        return params;
    }, [filters, searchValue, sortParam, filterKey]);

    const visit = useCallback(
        (
            params: QueryParams,
            { debounce = false, replace = false }: VisitOptions = {},
        ) => {
            clearDebounce();

            const run = () => {
                setIsLoading(true);
                router.get(url, params, {
                    only: [propKey],
                    preserveState: true,
                    preserveScroll: true,
                    replace,
                    onFinish: () => setIsLoading(false),
                });
            };

            if (debounce) {
                debounceTimer.current = setTimeout(run, debounceMs);

                return;
            }

            run();
        },
        [url, propKey, debounceMs, clearDebounce],
    );

    const onSortingChange: OnChangeFn<SortingState> = (updater) => {
        const next = typeof updater === 'function' ? updater(sorting) : updater;
        const params = currentParams();

        if (next.length === 0) {
            delete params.sort;
        } else {
            const { id, desc } = next[0];
            const field = sortFields[id] ?? id;

            params.sort = desc ? `-${field}` : field;
        }

        visit(params);
    };

    const onPaginationChange: OnChangeFn<PaginationState> = (updater) => {
        const next =
            typeof updater === 'function' ? updater(pagination) : updater;
        const params = currentParams();
        const nextPage = next.pageIndex + 1;

        if (nextPage > 1) {
            params.page = nextPage;
        }

        visit(params);
    };

    const onGlobalFilterChange = (value: string) => {
        setOverride(value);

        const params = currentParams();
        const filter =
            value === '' ? { ...filters } : { ...filters, [filterKey]: value };

        if (Object.keys(filter).length > 0) {
            params.filter = filter;
        } else {
            delete params.filter;
        }

        visit(params, { debounce: true, replace: true });
    };

    const setFilters = (next: Record<string, string>) => {
        const filter: Record<string, string> = {};

        for (const [key, value] of Object.entries(next)) {
            if (value !== '') {
                filter[key] = value;
            }
        }

        if (searchValue !== '') {
            filter[filterKey] = searchValue;
        }

        const params: QueryParams = {};

        if (Object.keys(filter).length > 0) {
            params.filter = filter;
        }

        if (sortParam !== '') {
            params.sort = sortParam;
        }

        visit(params);
    };

    return {
        data: paginated.data,
        pageCount: paginated.last_page ?? 1,
        rowCount: paginated.total ?? paginated.data.length,
        isLoading,
        sorting,
        onSortingChange,
        pagination,
        onPaginationChange,
        globalFilter: searchValue,
        onGlobalFilterChange,
        filters,
        setFilters,
    };
}
