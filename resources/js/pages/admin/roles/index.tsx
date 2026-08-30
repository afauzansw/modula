import { Form, Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { useState } from 'react';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import {
    DataTable,
    DataTableColumnHeader,
    useInertiaDataTable,
} from '@/components/data-table';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { bulkDestroy, create, edit, index } from '@/routes/admin/roles';

type RoleListItem = {
    id: number;
    name: string;
    is_system: boolean;
    permissions: string[];
};

/** Stable reference — maps the `name` column to the backend `sort` field. */
const sortFields = { name: 'name' };

const columns: ColumnDef<RoleListItem>[] = [
    {
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
    },
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
                    <Button variant="outline" size="sm" asChild>
                        <Link href={edit(row.original.id)}>Edit</Link>
                    </Button>
                    <DeleteRoleDialog role={row.original} />
                </div>
            ),
    },
];

export default function RolesIndex() {
    const source = useInertiaDataTable<RoleListItem>({
        propKey: 'roles',
        url: index().url,
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
                    <Button asChild>
                        <Link href={create()}>New role</Link>
                    </Button>
                </div>

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search roles…"
                    emptyMessage="No roles yet."
                    enableRowSelection={(role) => !role.is_system}
                    renderSelectionActions={({
                        selectedIds,
                        clearSelection,
                    }) => (
                        <BulkDeleteRolesDialog
                            ids={selectedIds.map(Number)}
                            onDeleted={clearSelection}
                        />
                    )}
                />
            </div>
        </>
    );
}

function DeleteRoleDialog({ role }: { role: RoleListItem }) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="destructive" size="sm">
                    Delete
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Delete &quot;{role.name}&quot;?</DialogTitle>
                <DialogDescription>
                    This permanently removes the role and revokes it from anyone
                    holding it. This cannot be undone.
                </DialogDescription>

                <Form
                    {...RoleController.destroy.form(role.id)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                disabled={processing}
                                asChild
                            >
                                <button type="submit">Delete role</button>
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function BulkDeleteRolesDialog({
    ids,
    onDeleted,
}: {
    ids: number[];
    onDeleted: () => void;
}) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const submit = () => {
        setProcessing(true);
        router.delete(bulkDestroy.url(), {
            data: { ids },
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);
                onDeleted();
            },
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="destructive" size="sm">
                    Delete {ids.length} selected
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>
                    Delete {ids.length} role{ids.length === 1 ? '' : 's'}?
                </DialogTitle>
                <DialogDescription>
                    This permanently removes the selected custom roles and
                    revokes them from anyone holding them. This cannot be
                    undone.
                </DialogDescription>

                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        disabled={processing}
                        onClick={submit}
                    >
                        Delete roles
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
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
