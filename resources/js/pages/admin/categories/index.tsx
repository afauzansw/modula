import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/admin/categories';
import type { CategoryListItem } from '@/types';
import { CategoryActions } from './components/category-actions';
import { CategoryFormDialog } from './components/category-form-dialog';

/** Stable reference — maps the `name` column to the backend `sort` field. */
const sortFields = { name: 'name' };

const columns: ColumnDef<CategoryListItem>[] = [
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
        accessorKey: 'slug',
        enableSorting: false,
        header: 'Slug',
        cell: ({ row }) => (
            <code className="text-xs text-muted-foreground">
                {row.original.slug}
            </code>
        ),
    },
    {
        id: 'actions',
        enableSorting: false,
        header: () => <span className="sr-only">Actions</span>,
        cell: ({ row }) => <CategoryActions category={row.original} />,
    },
];

export default function CategoriesIndex() {
    const source = useHttpDataTable<CategoryListItem>({
        fetchUrl: CategoryController.fetch.url(),
        filterKey: 'name',
        sortFields,
    });

    return (
        <>
            <Head title="Course Category" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between gap-4">
                    <Heading
                        title="Course Category"
                        description="Manage the categories courses are organized under"
                        className="mb-0"
                    />
                    <CategoryFormDialog
                        trigger={<Button>New category</Button>}
                    />
                </div>

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search categories…"
                    emptyMessage="No categories yet."
                    canSelect
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
                            title={`Delete ${selectedIds.length} categor${
                                selectedIds.length === 1 ? 'y' : 'ies'
                            }?`}
                            description="Courses in the selected categories become uncategorized. This cannot be undone."
                            form={CategoryController.bulkDestroy.form()}
                            fields={{ ids: selectedIds.map(Number) }}
                            confirmLabel="Delete categories"
                            onConfirmed={clearSelection}
                        />
                    )}
                />
            </div>
        </>
    );
}

CategoriesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Course Category',
            href: index(),
        },
    ],
};
