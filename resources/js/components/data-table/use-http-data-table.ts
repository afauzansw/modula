import { router, useHttp, usePage } from '@inertiajs/react';
import type {
    OnChangeFn,
    PaginationState,
    SortingState,
} from '@tanstack/react-table';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { Paginated } from '@/types';
import type { DataTableSource } from './types';

type UseHttpDataTableOptions = {
    /** JSON endpoint the table pulls pages from — usually `someRoute.fetch().url`. */
    fetchUrl: string;
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
    /** Page size assumed until the first response lands. */
    fallbackPageSize?: number;
};

function queryString(url: string): URLSearchParams {
    return new URLSearchParams(
        url.includes('?') ? url.slice(url.indexOf('?') + 1) : '',
    );
}

/**
 * `DataTableSource` backed by a standalone JSON fetch (`useHttp`) against a
 * dedicated endpoint. Search, sort and page live in the URL query string
 * (shareable, back-button safe); each change is an Inertia client-side visit
 * (`router.push` / `router.replace`) that only rewrites the URL — no server
 * round-trip for the page shell — and a `useEffect` keyed on the query string
 * re-fetches the table's rows as plain JSON.
 *
 * Sibling of `useInertiaDataTable`; both satisfy `DataTableSource`, so
 * `<DataTable>` doesn't care which one feeds it.
 */
export function useHttpDataTable<TData>({
    fetchUrl,
    filterKey,
    sortFields = {},
    debounceMs = 300,
    fallbackPageSize = 15,
}: UseHttpDataTableOptions): DataTableSource<TData> {
    const page = usePage();
    const { get, processing } = useHttp<
        Record<string, never>,
        Paginated<TData>
    >({});

    const [payload, setPayload] = useState<Paginated<TData> | null>(null);
    const [settled, setSettled] = useState(false);

    const currentQuery = queryString(page.url).toString();

    // Re-fetch whenever the query string changes: mount, our own
    // router.push/replace, and browser back/forward all surface here.
    useEffect(() => {
        let active = true;

        get(currentQuery ? `${fetchUrl}?${currentQuery}` : fetchUrl, {
            onSuccess: (data) => {
                if (active) {
                    setPayload(data);
                }
            },
            onFinish: () => {
                if (active) {
                    setSettled(true);
                }
            },
        });

        return () => {
            active = false;
        };
        // `get` is a stable ref from useHttp; keying on its identity would loop.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [fetchUrl, currentQuery]);

    // Loading until the first response settles, then mirrors any in-flight reload.
    const isLoading = !settled || processing;

    const params = useMemo(() => queryString(page.url), [page.url]);
    const urlSearch = params.get(`filter[${filterKey}]`) ?? '';
    const sortParam = params.get('sort') ?? '';
    const pageParam = Math.max(1, Number(params.get('page') ?? '1') || 1);

    // Every `filter[*]` param except the search key drives the filter card.
    const filters = useMemo(() => {
        const out: Record<string, string> = {};

        for (const [key, value] of params.entries()) {
            const match = key.match(/^filter\[(.+)\]$/);

            if (match && match[1] !== filterKey) {
                out[match[1]] = value;
            }
        }

        return out;
    }, [params, filterKey]);

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
        pageIndex: pageParam - 1,
        pageSize: payload?.per_page ?? fallbackPageSize,
    };

    // The search box shows `override` while a debounced reload is pending, then
    // falls back to the URL — same trick as `useInertiaDataTable`.
    const [override, setOverride] = useState<string | null>(null);
    const searchValue = override ?? urlSearch;

    if (override !== null && override === urlSearch) {
        setOverride(null);
    }

    const debounceTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const clearDebounce = useCallback(() => {
        if (debounceTimer.current) {
            clearTimeout(debounceTimer.current);
            debounceTimer.current = null;
        }
    }, []);

    useEffect(() => clearDebounce, [clearDebounce]);

    const pathname = page.url.split('?')[0];

    const navigate = useCallback(
        (next: URLSearchParams, { replace = false } = {}) => {
            clearDebounce();

            const qs = next.toString();
            const url = qs ? `${pathname}?${qs}` : pathname;

            router[replace ? 'replace' : 'push']({
                url,
                preserveScroll: true,
                preserveState: true,
            });
        },
        [clearDebounce, pathname],
    );

    const currentParams = useCallback((): URLSearchParams => {
        const next = new URLSearchParams();

        for (const [key, value] of Object.entries(filters)) {
            next.set(`filter[${key}]`, value);
        }

        if (searchValue !== '') {
            next.set(`filter[${filterKey}]`, searchValue);
        }

        if (sortParam !== '') {
            next.set('sort', sortParam);
        }

        return next;
    }, [filters, searchValue, sortParam, filterKey]);

    const onSortingChange: OnChangeFn<SortingState> = (updater) => {
        const next = typeof updater === 'function' ? updater(sorting) : updater;
        const nextParams = currentParams();

        if (next.length === 0) {
            nextParams.delete('sort');
        } else {
            const { id, desc } = next[0];
            const field = sortFields[id] ?? id;

            nextParams.set('sort', desc ? `-${field}` : field);
        }

        navigate(nextParams);
    };

    const onPaginationChange: OnChangeFn<PaginationState> = (updater) => {
        const next =
            typeof updater === 'function' ? updater(pagination) : updater;
        const nextParams = currentParams();
        const nextPage = next.pageIndex + 1;

        if (nextPage > 1) {
            nextParams.set('page', String(nextPage));
        }

        navigate(nextParams);
    };

    const onGlobalFilterChange = (value: string) => {
        setOverride(value);

        const nextParams = currentParams();

        if (value === '') {
            nextParams.delete(`filter[${filterKey}]`);
        } else {
            nextParams.set(`filter[${filterKey}]`, value);
        }

        clearDebounce();
        debounceTimer.current = setTimeout(
            () => navigate(nextParams, { replace: true }),
            debounceMs,
        );
    };

    const setFilters = (next: Record<string, string>) => {
        const nextParams = new URLSearchParams();

        for (const [key, value] of Object.entries(next)) {
            if (value !== '') {
                nextParams.set(`filter[${key}]`, value);
            }
        }

        if (searchValue !== '') {
            nextParams.set(`filter[${filterKey}]`, searchValue);
        }

        if (sortParam !== '') {
            nextParams.set('sort', sortParam);
        }

        navigate(nextParams);
    };

    return {
        data: payload?.data ?? [],
        pageCount: payload?.last_page ?? 1,
        rowCount: payload?.total ?? payload?.data.length ?? 0,
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
