import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';

type DataTablePaginationProps = {
    pageIndex: number;
    pageCount: number;
    rowCount: number;
    selectedCount: number;
    canPreviousPage: boolean;
    canNextPage: boolean;
    onPreviousPage: () => void;
    onNextPage: () => void;
};

/**
 * Plain-value props only — never the TanStack `table` object. Passing the (stable,
 * internally-mutated) table instance into a child breaks under React Compiler,
 * which memoizes the child on the unchanging reference. `DataTable` reads the
 * live values and hands them down.
 */
export function DataTablePagination({
    pageIndex,
    pageCount,
    rowCount,
    selectedCount,
    canPreviousPage,
    canNextPage,
    onPreviousPage,
    onNextPage,
}: DataTablePaginationProps) {
    return (
        <div className="flex items-center justify-between gap-4">
            <p className="text-sm text-muted-foreground">
                {selectedCount > 0
                    ? `${selectedCount} of ${rowCount} row(s) selected`
                    : `${rowCount} row(s)`}
            </p>

            <div className="flex items-center gap-2">
                <span className="text-sm text-muted-foreground">
                    Page {pageIndex + 1} of {Math.max(1, pageCount)}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={onPreviousPage}
                    disabled={!canPreviousPage}
                >
                    <ChevronLeft />
                    <span className="sr-only">Previous page</span>
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={onNextPage}
                    disabled={!canNextPage}
                >
                    <ChevronRight />
                    <span className="sr-only">Next page</span>
                </Button>
            </div>
        </div>
    );
}
