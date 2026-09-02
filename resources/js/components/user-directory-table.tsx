import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { ConfirmDialog } from '@/components/confirm-dialog';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { UserListItem } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const joined = new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' });

/** Stable reference — maps sortable columns to their backend `sort` field. */
const sortFields = { name: 'name', email: 'email', created_at: 'created_at' };

const columns: ColumnDef<UserListItem>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Name"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) => (
            <span className="font-medium">{row.original.name}</span>
        ),
    },
    {
        accessorKey: 'email',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Email"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) => (
            <span className="text-muted-foreground">{row.original.email}</span>
        ),
    },
    {
        accessorKey: 'is_blocked',
        enableSorting: false,
        header: 'Status',
        cell: ({ row }) => (
            <Badge
                variant={row.original.is_blocked ? 'destructive' : 'outline'}
            >
                {row.original.is_blocked ? 'Blocked' : 'Active'}
            </Badge>
        ),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Joined"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) => (
            <span className="text-muted-foreground">
                {row.original.created_at
                    ? joined.format(new Date(row.original.created_at))
                    : '—'}
            </span>
        ),
    },
];

type Props = {
    title: string;
    description: string;
    /** The role in the copy — "student" / "instructor". */
    noun: string;
    fetchUrl: string;
    /** `{Controller}.bulkUpdateStatus.form()` — submitted with `ids` + `is_blocked`. */
    bulkStatusForm: RouteFormDefinition<'post'>;
};

/**
 * The admin directory for one user role: a searchable table of name / email /
 * status / joined, with a bulk Block / Unblock action over the selection.
 * Backs both `admin/students` and `admin/instructors`.
 */
export function UserDirectoryTable({
    title,
    description,
    noun,
    fetchUrl,
    bulkStatusForm,
}: Props) {
    const source = useHttpDataTable<UserListItem>({
        fetchUrl,
        filterKey: 'search',
        sortFields,
    });

    return (
        <>
            <Head title={title} />

            <div className="space-y-6 p-4">
                <Heading title={title} description={description} />

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder={`Search ${noun}s by name or email…`}
                    emptyMessage={`No ${noun}s yet.`}
                    canSelect
                    renderSelectionActions={({
                        selectedIds,
                        clearSelection,
                    }) => (
                        <>
                            <ConfirmDialog
                                trigger={
                                    <Button variant="destructive" size="sm">
                                        Block {selectedIds.length}
                                    </Button>
                                }
                                title={`Block ${selectedIds.length} ${noun}${
                                    selectedIds.length === 1 ? '' : 's'
                                }?`}
                                description={`Blocked ${noun}s stay in the system but are flagged as blocked.`}
                                form={bulkStatusForm}
                                fields={{
                                    ids: selectedIds.map(Number),
                                    is_blocked: 1,
                                }}
                                confirmLabel="Block"
                                onConfirmed={clearSelection}
                            />
                            <ConfirmDialog
                                trigger={
                                    <Button variant="outline" size="sm">
                                        Unblock {selectedIds.length}
                                    </Button>
                                }
                                title={`Unblock ${selectedIds.length} ${noun}${
                                    selectedIds.length === 1 ? '' : 's'
                                }?`}
                                description={`The selected ${noun}s are no longer flagged as blocked.`}
                                form={bulkStatusForm}
                                fields={{
                                    ids: selectedIds.map(Number),
                                    is_blocked: 0,
                                }}
                                confirmLabel="Unblock"
                                confirmVariant="default"
                                onConfirmed={clearSelection}
                            />
                        </>
                    )}
                />
            </div>
        </>
    );
}
