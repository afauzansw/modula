import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/admin/roles';
import type { Role } from '@/types';
import { RoleFormDialog } from './components/role-form-dialog';

/** Stable reference — maps the `name` column to the backend `sort` field. */
const sortFields = { name: 'name' };

const columns: ColumnDef<Role>[] = [
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
        accessorKey: 'is_system',
        enableSorting: false,
        header: 'Type',
        cell: ({ row }) => (
            <Badge variant={row.original.is_system ? 'secondary' : 'outline'}>
                {row.original.is_system ? 'System' : 'Custom'}
            </Badge>
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
        cell: ({ row }) =>
            row.original.is_system ? null : (
                <div className="flex justify-end gap-2">
                    <RoleFormDialog
                        role={row.original}
                        trigger={
                            <Button variant="outline" size="sm">
                                Edit
                            </Button>
                        }
                    />
                    <ConfirmDialog
                        trigger={
                            <Button variant="destructive" size="sm">
                                Delete
                            </Button>
                        }
                        title={`Delete "${row.original.name}"?`}
                        description="This permanently removes the role and revokes it from anyone holding it. This cannot be undone."
                        form={RoleController.destroy.form(row.original.id)}
                        confirmLabel="Delete role"
                    />
                </div>
            ),
    },
];

export default function RolesIndex() {
    const source = useHttpDataTable<Role>({
        fetchUrl: RoleController.fetch.url(),
        filterKey: 'name',
        sortFields,
    });

    return (
        <>
            <Head title="Roles & Permissions" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Roles & Permissions"
                        description="Manage custom admin roles and the permissions they carry"
                    />
                    <RoleFormDialog trigger={<Button>New role</Button>} />
                </div>

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search roles…"
                    emptyMessage="No roles yet."
                    canSelect={(role) => !role.is_system}
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
                            title={`Delete ${selectedIds.length} role${
                                selectedIds.length === 1 ? '' : 's'
                            }?`}
                            description="This permanently removes the selected custom roles and revokes them from anyone holding them. This cannot be undone."
                            form={RoleController.bulkDestroy.form()}
                            fields={{ ids: selectedIds.map(Number) }}
                            confirmLabel="Delete roles"
                            onConfirmed={clearSelection}
                        />
                    )}
                />
            </div>
        </>
    );
}

RolesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Roles & Permissions',
            href: index(),
        },
    ],
};
