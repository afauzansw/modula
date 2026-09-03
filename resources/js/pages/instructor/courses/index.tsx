import { Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import CourseController from '@/actions/App/Http/Controllers/Instructor/CourseController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import type { DataTableFilterDef } from '@/components/data-table';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useInstructorCourseCategories } from '@/hooks/use-instructor-course-categories';
import { create, index } from '@/routes/instructor/courses';
import type { CourseStatus, InstructorCourseListItem } from '@/types';
import { CourseActions } from './components/course-actions';
import { formatPrice } from './lib/format-price';

/** Stable reference — maps sortable columns to their backend `sort` field. */
const sortFields = { title: 'title', price: 'price' };

const statusVariant: Record<CourseStatus, 'default' | 'secondary' | 'outline'> =
    {
        published: 'default',
        draft: 'outline',
        archived: 'secondary',
    };

const columns: ColumnDef<InstructorCourseListItem>[] = [
    {
        accessorKey: 'title',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Title"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) => (
            <span className="font-medium">{row.original.title}</span>
        ),
    },
    {
        accessorKey: 'category',
        enableSorting: false,
        header: 'Category',
        cell: ({ row }) => (
            <span className="text-muted-foreground">
                {row.original.category ?? 'Uncategorized'}
            </span>
        ),
    },
    {
        accessorKey: 'price',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Price"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) =>
            formatPrice(row.original.price, row.original.is_free),
    },
    {
        accessorKey: 'status',
        enableSorting: false,
        header: 'Status',
        cell: ({ row }) => (
            <Badge
                variant={statusVariant[row.original.status]}
                className="capitalize"
            >
                {row.original.status}
            </Badge>
        ),
    },
    {
        id: 'actions',
        enableSorting: false,
        header: () => <span className="sr-only">Actions</span>,
        cell: ({ row }) => <CourseActions course={row.original} />,
    },
];

export default function InstructorCourses() {
    const source = useHttpDataTable<InstructorCourseListItem>({
        fetchUrl: CourseController.fetch.url(),
        filterKey: 'title',
        sortFields,
    });

    const { categories } = useInstructorCourseCategories();

    const filters: DataTableFilterDef[] = [
        {
            key: 'category_id',
            label: 'Category',
            type: 'select',
            options: categories.map((category) => ({
                label: category.name,
                value: String(category.id),
            })),
        },
        {
            key: 'is_free',
            label: 'Price',
            type: 'select',
            options: [
                { label: 'Free', value: '1' },
                { label: 'Paid', value: '0' },
            ],
        },
        {
            key: 'status',
            label: 'Status',
            type: 'select',
            options: [
                { label: 'Draft', value: 'draft' },
                { label: 'Published', value: 'published' },
                { label: 'Archived', value: 'archived' },
            ],
        },
    ];

    return (
        <>
            <Head title="Courses" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between gap-4">
                    <Heading
                        title="Courses"
                        description="Courses you author and manage"
                        className="mb-0"
                    />
                    <Button asChild>
                        <Link href={create()}>New course</Link>
                    </Button>
                </div>

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search courses by title…"
                    emptyMessage="No courses yet."
                    canSelect
                    filters={filters}
                    renderSelectionActions={({
                        selectedIds,
                        clearSelection,
                    }) => (
                        <>
                            <ConfirmDialog
                                trigger={
                                    <Button variant="outline" size="sm">
                                        Publish {selectedIds.length}
                                    </Button>
                                }
                                title={`Publish ${selectedIds.length} course${
                                    selectedIds.length === 1 ? '' : 's'
                                }?`}
                                description="Published courses appear in the catalog and can be enrolled in."
                                form={CourseController.bulkUpdateStatus.form()}
                                fields={{
                                    ids: selectedIds.map(Number),
                                    status: 'published',
                                }}
                                confirmLabel="Publish"
                                confirmVariant="default"
                                onConfirmed={clearSelection}
                            />
                            <ConfirmDialog
                                trigger={
                                    <Button variant="outline" size="sm">
                                        Unpublish {selectedIds.length}
                                    </Button>
                                }
                                title={`Unpublish ${selectedIds.length} course${
                                    selectedIds.length === 1 ? '' : 's'
                                }?`}
                                description="Unpublished courses go back to draft and drop out of the catalog."
                                form={CourseController.bulkUpdateStatus.form()}
                                fields={{
                                    ids: selectedIds.map(Number),
                                    status: 'draft',
                                }}
                                confirmLabel="Unpublish"
                                confirmVariant="default"
                                onConfirmed={clearSelection}
                            />
                            <ConfirmDialog
                                trigger={
                                    <Button variant="destructive" size="sm">
                                        Delete {selectedIds.length}
                                    </Button>
                                }
                                title={`Delete ${selectedIds.length} course${
                                    selectedIds.length === 1 ? '' : 's'
                                }?`}
                                description="This permanently removes the selected courses and everything under them. This cannot be undone."
                                form={CourseController.bulkDestroy.form()}
                                fields={{ ids: selectedIds.map(Number) }}
                                confirmLabel="Delete courses"
                                onConfirmed={clearSelection}
                            />
                        </>
                    )}
                />
            </div>
        </>
    );
}

InstructorCourses.layout = {
    breadcrumbs: [
        {
            title: 'Courses',
            href: index(),
        },
    ],
};
