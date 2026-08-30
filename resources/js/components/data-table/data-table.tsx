import {
    flexRender,
    getCoreRowModel,
    useReactTable,
} from '@tanstack/react-table';
import type { ColumnDef, Row, RowSelectionState } from '@tanstack/react-table';
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';
import { DataTablePagination } from './data-table-pagination';
import type { DataTableSource } from './types';

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
    /** `true` for every row, or a predicate to make some rows unselectable. */
    enableRowSelection?: boolean | ((row: TData) => boolean);
    /** Toolbar content shown while ≥1 row is selected (e.g. a bulk action). */
    renderSelectionActions?: (
        ctx: SelectionActionsContext<TData>,
    ) => React.ReactNode;
};

export function DataTable<TData extends { id: number | string }, TValue>({
    columns,
    source,
    searchPlaceholder = 'Search…',
    emptyMessage = 'No results.',
    enableRowSelection,
    renderSelectionActions,
}: DataTableProps<TData, TValue>) {
    // TanStack Table v8 mutates one long-lived `table` object rather than
    // returning fresh values, so React Compiler can't memoize this component
    // safely — opt it out explicitly and only hand plain values to children.
    'use no memo';

    const [rowSelection, setRowSelection] = useState<RowSelectionState>({});

    // eslint-disable-next-line react-hooks/incompatible-library
    const table = useReactTable({
        data: source.data,
        columns,
        pageCount: source.pageCount,
        rowCount: source.rowCount,
        state: {
            sorting: source.sorting,
            pagination: source.pagination,
            rowSelection,
        },
        getRowId: (row) => String(row.id),
        enableRowSelection:
            typeof enableRowSelection === 'function'
                ? (row: Row<TData>) => enableRowSelection(row.original)
                : enableRowSelection,
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

    return (
        <div className="space-y-4">
            <div className="flex items-center gap-2">
                <Input
                    value={source.globalFilter}
                    onChange={(event) =>
                        source.onGlobalFilterChange(event.target.value)
                    }
                    placeholder={searchPlaceholder}
                    className="max-w-xs"
                />
                {selectedIds.length > 0 &&
                    renderSelectionActions?.({
                        selectedIds,
                        selectedRows: table
                            .getSelectedRowModel()
                            .rows.map((row) => row.original),
                        clearSelection,
                    })}
            </div>

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
                        {rows.length === 0 ? (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="h-24 text-center text-muted-foreground"
                                >
                                    {emptyMessage}
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
