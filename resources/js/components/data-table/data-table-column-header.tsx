import type { SortDirection } from '@tanstack/react-table';
import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type DataTableColumnHeaderProps = {
    title: string;
    canSort: boolean;
    sorted: false | SortDirection;
    onToggleSort?: (event: unknown) => void;
    className?: string;
};

/**
 * Plain-value props only — the caller pulls `canSort` / `sorted` /
 * `onToggleSort` off the TanStack column so this stays React Compiler-safe (see
 * DataTablePagination for why the table object must not cross a component
 * boundary).
 */
export function DataTableColumnHeader({
    title,
    canSort,
    sorted,
    onToggleSort,
    className,
}: DataTableColumnHeaderProps) {
    if (!canSort) {
        return <span className={className}>{title}</span>;
    }

    return (
        <Button
            variant="ghost"
            size="sm"
            className={cn('-ml-2 h-8', className)}
            onClick={onToggleSort}
        >
            <span>{title}</span>
            {sorted === 'asc' ? (
                <ArrowUp className="text-muted-foreground" />
            ) : sorted === 'desc' ? (
                <ArrowDown className="text-muted-foreground" />
            ) : (
                <ChevronsUpDown className="text-muted-foreground/50" />
            )}
        </Button>
    );
}
