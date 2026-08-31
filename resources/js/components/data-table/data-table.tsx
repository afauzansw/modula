import {
    flexRender,
    getCoreRowModel,
    useReactTable,
} from '@tanstack/react-table';
import type { ColumnDef, Row, RowSelectionState } from '@tanstack/react-table';
import {
    LayoutGrid,
    Rows3,
    SlidersHorizontal,
    Table as TableIcon,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { cn } from '@/lib/utils';
import { DataTableFilters } from './data-table-filters';
import { DataTablePagination } from './data-table-pagination';
import type {
    DataTableFilterDef,
    DataTableSource,
    DataTableView,
} from './types';

const DEFAULT_VIEWS: DataTableView[] = ['table', 'list', 'grid'];

/** Leading checkbox column — prepended by `<DataTable canSelect>`. */
function selectColumn<TData, TValue>(): ColumnDef<TData, TValue> {
    return {
        id: 'select',
        enableSorting: false,
        header: ({ table }) => (
            <Checkbox
                checked={
                    table.getIsAllPageRowsSelected()
                        ? true
                        : table.getIsSomePageRowsSelected()
                          ? 'indeterminate'
                          : false
                }
                onCheckedChange={(value) =>
                    table.toggleAllPageRowsSelected(value === true)
                }
                aria-label="Select all"
            />
        ),
        cell: ({ row }) => (
            <Checkbox
                checked={row.getIsSelected()}
                disabled={!row.getCanSelect()}
                onCheckedChange={(value) => row.toggleSelected(value === true)}
                aria-label="Select row"
            />
        ),
    };
}

type SelectionActionsContext<TData> = {
    /** Selected row ids (as strings — `getRowId` stringifies `row.id`). */
    selectedIds: string[];
    /** Selected rows that are on the current page. */
    selectedRows: TData[];
    clearSelection: () => void;
};

type DataTableProps<TData extends { id: number | string }, TValue> = {
    columns: ColumnDef<TData, TValue>[];
    source: DataTableSource<TData>;
    searchPlaceholder?: string;
    emptyMessage?: string;
    /**
     * Prepend a checkbox column for row selection. `true` selects every row; a
     * predicate makes some rows unselectable (their checkbox is disabled). In
     * the list/grid views the checkbox is overlaid on each card.
     */
    canSelect?: boolean | ((row: TData) => boolean);
    /** Toolbar content shown while ≥1 row is selected (e.g. a bulk action). */
    renderSelectionActions?: (
        ctx: SelectionActionsContext<TData>,
    ) => React.ReactNode;
    /** Filter-card controls. Each `key` must be an allowed repository filter. */
    filters?: DataTableFilterDef[];
    /** Card renderer for the list/grid views. Passing it shows the view toggle. */
    renderCard?: (row: TData) => React.ReactNode;
    /** Views offered when `renderCard` is set. Default: table, list, grid. */
    views?: DataTableView[];
    /** localStorage key to remember the chosen view per viewer. */
    viewStorageKey?: string;
};

export function DataTable<TData extends { id: number | string }, TValue>({
    columns,
    source,
    searchPlaceholder = 'Search…',
    emptyMessage = 'No results.',
    canSelect,
    renderSelectionActions,
    filters,
    renderCard,
    views,
    viewStorageKey,
}: DataTableProps<TData, TValue>) {
    // TanStack Table v8 mutates one long-lived `table` object rather than
    // returning fresh values, so React Compiler can't memoize this component
    // safely — opt it out explicitly and only hand plain values to children.
    'use no memo';

    const availableViews = views ?? DEFAULT_VIEWS;
    const showViewToggle =
        renderCard !== undefined && availableViews.length > 1;

    const [rowSelection, setRowSelection] = useState<RowSelectionState>({});
    const [filtersOpen, setFiltersOpen] = useState(false);
    const [storedView, setStoredView] = useState<DataTableView>(() => {
        if (viewStorageKey) {
            try {
                const stored = localStorage.getItem(viewStorageKey);

                if (
                    stored &&
                    availableViews.includes(stored as DataTableView)
                ) {
                    return stored as DataTableView;
                }
            } catch {
                // localStorage may be unavailable — fall through to the default.
            }
        }

        return availableViews[0] ?? 'table';
    });

    const view: DataTableView = renderCard ? storedView : 'table';

    const setView = (next: DataTableView) => {
        setStoredView(next);

        if (viewStorageKey) {
            try {
                localStorage.setItem(viewStorageKey, next);
            } catch {
                // ignore
            }
        }
    };

    const tableColumns = useMemo(
        () =>
            canSelect ? [selectColumn<TData, TValue>(), ...columns] : columns,
        [canSelect, columns],
    );

    // eslint-disable-next-line react-hooks/incompatible-library
    const table = useReactTable({
        data: source.data,
        columns: tableColumns,
        pageCount: source.pageCount,
        rowCount: source.rowCount,
        state: {
            sorting: source.sorting,
            pagination: source.pagination,
            rowSelection,
        },
        getRowId: (row) => String(row.id),
        enableRowSelection:
            typeof canSelect === 'function'
                ? (row: Row<TData>) => canSelect(row.original)
                : canSelect,
        manualPagination: true,
        manualSorting: true,
        manualFiltering: true,
        onRowSelectionChange: setRowSelection,
        onSortingChange: source.onSortingChange,
        onPaginationChange: source.onPaginationChange,
        getCoreRowModel: getCoreRowModel(),
    });

    const selectedIds = Object.entries(rowSelection)
        .filter(([, selected]) => selected)
        .map(([id]) => id);

    const clearSelection = () => setRowSelection({});

    const rows = table.getRowModel().rows;
    const isEmpty = rows.length === 0;
    const emptyText = source.isLoading ? 'Loading…' : emptyMessage;
    const activeFilterCount = Object.keys(source.filters).length;

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-center gap-2">
                <Input
                    value={source.globalFilter}
                    onChange={(event) =>
                        source.onGlobalFilterChange(event.target.value)
                    }
                    placeholder={searchPlaceholder}
                    className="max-w-xs"
                />

                {filters && filters.length > 0 && (
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setFiltersOpen((open) => !open)}
                        aria-expanded={filtersOpen}
                    >
                        <SlidersHorizontal className="size-4" />
                        Filter
                        {activeFilterCount > 0 && (
                            <span className="ml-1 rounded bg-primary/10 px-1.5 text-xs font-medium">
                                {activeFilterCount}
                            </span>
                        )}
                    </Button>
                )}

                {selectedIds.length > 0 &&
                    renderSelectionActions?.({
                        selectedIds,
                        selectedRows: table
                            .getSelectedRowModel()
                            .rows.map((row) => row.original),
                        clearSelection,
                    })}

                {showViewToggle && (
                    <ToggleGroup
                        type="single"
                        variant="outline"
                        size="sm"
                        value={view}
                        onValueChange={(next) =>
                            next && setView(next as DataTableView)
                        }
                        className="ml-auto"
                    >
                        {availableViews.includes('table') && (
                            <ToggleGroupItem
                                value="table"
                                aria-label="Table view"
                            >
                                <TableIcon className="size-4" />
                            </ToggleGroupItem>
                        )}
                        {availableViews.includes('list') && (
                            <ToggleGroupItem
                                value="list"
                                aria-label="List view"
                            >
                                <Rows3 className="size-4" />
                            </ToggleGroupItem>
                        )}
                        {availableViews.includes('grid') && (
                            <ToggleGroupItem
                                value="grid"
                                aria-label="Grid view"
                            >
                                <LayoutGrid className="size-4" />
                            </ToggleGroupItem>
                        )}
                    </ToggleGroup>
                )}
            </div>

            {filtersOpen && filters && filters.length > 0 && (
                <DataTableFilters
                    filters={filters}
                    values={source.filters}
                    onApply={source.setFilters}
                />
            )}

            {view === 'table' ? (
                <div
                    className={cn(
                        'rounded-lg border transition-opacity',
                        source.isLoading && 'pointer-events-none opacity-60',
                    )}
                >
                    <Table>
                        <TableHeader>
                            {table.getHeaderGroups().map((headerGroup) => (
                                <TableRow key={headerGroup.id}>
                                    {headerGroup.headers.map((header) => (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                      header.column.columnDef
                                                          .header,
                                                      header.getContext(),
                                                  )}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {isEmpty ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={tableColumns.length}
                                        className="h-24 text-center text-muted-foreground"
                                    >
                                        {emptyText}
                                    </TableCell>
                                </TableRow>
                            ) : (
                                rows.map((row) => (
                                    <TableRow
                                        key={row.id}
                                        data-state={
                                            row.getIsSelected()
                                                ? 'selected'
                                                : undefined
                                        }
                                    >
                                        {row.getVisibleCells().map((cell) => (
                                            <TableCell key={cell.id}>
                                                {flexRender(
                                                    cell.column.columnDef.cell,
                                                    cell.getContext(),
                                                )}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            ) : (
                <div
                    className={cn(
                        'transition-opacity',
                        source.isLoading && 'pointer-events-none opacity-60',
                    )}
                >
                    {isEmpty ? (
                        <div className="flex h-24 items-center justify-center rounded-lg border text-sm text-muted-foreground">
                            {emptyText}
                        </div>
                    ) : (
                        <div
                            className={
                                view === 'grid'
                                    ? 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3'
                                    : 'space-y-2'
                            }
                        >
                            {rows.map((row) => (
                                <CardRow
                                    key={row.id}
                                    row={row}
                                    renderCard={renderCard!}
                                    selectable={canSelect !== undefined}
                                />
                            ))}
                        </div>
                    )}
                </div>
            )}

            <DataTablePagination
                pageIndex={table.getState().pagination.pageIndex}
                pageCount={table.getPageCount()}
                rowCount={table.getRowCount()}
                selectedCount={selectedIds.length}
                canPreviousPage={table.getCanPreviousPage()}
                canNextPage={table.getCanNextPage()}
                onPreviousPage={() => table.previousPage()}
                onNextPage={() => table.nextPage()}
            />
        </div>
    );
}

function CardRow<TData extends { id: number | string }>({
    row,
    renderCard,
    selectable,
}: {
    row: Row<TData>;
    renderCard: (row: TData) => React.ReactNode;
    selectable: boolean;
}) {
    if (!selectable) {
        return <>{renderCard(row.original)}</>;
    }

    return (
        <div className="relative">
            <Checkbox
                className="absolute top-3 left-3 z-10 bg-background"
                checked={row.getIsSelected()}
                disabled={!row.getCanSelect()}
                onCheckedChange={(value) => row.toggleSelected(value === true)}
                aria-label="Select row"
            />
            {renderCard(row.original)}
        </div>
    );
}
