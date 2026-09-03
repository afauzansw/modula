import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import AdminUserController from '@/actions/App/Http/Controllers/Admin/AdminUserController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import type { DataTableFilterDef } from '@/components/data-table';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { usePermissionCatalogue } from '@/hooks/use-permission-catalogue';
import { index } from '@/routes/admin/admins';
import type { AdminUserListItem } from '@/types';
import { AdminActions } from './components/admin-actions';
import { AdminFormDialog } from './components/admin-form-dialog';

/** Stable reference — maps sortable columns to their backend `sort` field. */
const sortFields = { name: 'name', email: 'email' };

const columns: ColumnDef<AdminUserListItem>[] = [
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
        accessorKey: 'permissions',
        enableSorting: false,
        header: 'Permissions',
        cell: ({ row }) => {
            const count = row.original.permissions.length;

            return (
                <span className="text-muted-foreground">
                    {count} permission{count === 1 ? '' : 's'}
                </span>
            );
        },
    },
    {
        id: 'actions',
        enableSorting: false,
        header: () => <span className="sr-only">Actions</span>,
        cell: ({ row }) => <AdminActions admin={row.original} />,
    },
];

export default function AdminsIndex() {
    const source = useHttpDataTable<AdminUserListItem>({
        fetchUrl: AdminUserController.fetch.url(),
        filterKey: 'name',
        sortFields,
    });

    const { permissions } = usePermissionCatalogue();

    const filters: DataTableFilterDef[] = [
        {
            key: 'email',
            label: 'Email',
            type: 'text',
            placeholder: 'name@example.com',
        },
        {
            key: 'permission',
            label: 'Permission',
            type: 'select',
            options: Object.entries(permissions ?? {}).map(
                ([value, label]) => ({
                    label,
                    value,
                }),
            ),
        },
    ];

    return (
        <>
            <Head title="Admins" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between gap-4">
                    <Heading
                        title="Admins"
                        description="Accounts with access to the admin panel"
                        className="mb-0"
                    />
                    <AdminFormDialog trigger={<Button>New admin</Button>} />
                </div>

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search admins by name…"
                    emptyMessage="No admins yet."
                    canSelect
                    filters={filters}
                    renderSelectionActions={({
                        selectedIds,
                        clearSelection,
                    }) => (
                        <ConfirmDialog
                            trigger={
                                <Button variant="destructive" size="sm">
                                    Delete {selectedIds.length} selected
                                </Button>
                            }
                            title={`Delete ${selectedIds.length} admin${
                                selectedIds.length === 1 ? '' : 's'
                            }?`}
                            description="This permanently removes the selected accounts and their admin-panel access. Your own account is skipped. This cannot be undone."
                            form={AdminUserController.bulkDestroy.form()}
                            fields={{ ids: selectedIds.map(Number) }}
                            confirmLabel="Delete admins"
                            onConfirmed={clearSelection}
                        />
                    )}
                />
            </div>
        </>
    );
}

AdminsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Admins',
            href: index(),
        },
    ],
};
