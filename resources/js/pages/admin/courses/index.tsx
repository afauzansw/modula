import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import type { DataTableFilterDef } from '@/components/data-table';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { useCourseCategories } from '@/hooks/use-course-categories';
import { index } from '@/routes/admin/courses';
import type { CourseListItem } from '@/types';
import { CourseCard } from './components/course-card';
import { formatPrice } from './lib/format-price';

/** Stable reference — maps sortable columns to their backend `sort` field. */
const sortFields = { title: 'title', price: 'price' };

const columns: ColumnDef<CourseListItem>[] = [
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
        accessorKey: 'instructor',
        enableSorting: false,
        header: 'Instructor',
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
            <Badge variant="outline" className="capitalize">
                {row.original.status}
            </Badge>
        ),
    },
];

export default function CoursesIndex() {
    const source = useHttpDataTable<CourseListItem>({
        fetchUrl: CourseController.fetch.url(),
        filterKey: 'title',
        sortFields,
    });

    const { categories } = useCourseCategories();

    const filters: DataTableFilterDef[] = [
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
        {
            key: 'is_free',
            label: 'Access',
            type: 'select',
            options: [
                { label: 'Free', value: '1' },
                { label: 'Paid', value: '0' },
            ],
        },
        {
            key: 'category_id',
            label: 'Category',
            type: 'select',
            options: categories.map((category) => ({
                label: category.name,
                value: String(category.id),
            })),
        },
    ];

    return (
        <>
            <Head title="Courses" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Courses"
                    description="Manage the course catalog"
                />

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search courses…"
                    emptyMessage="No courses yet."
                    views={['table', 'grid']}
                    renderCard={(course) => <CourseCard course={course} />}
                    viewStorageKey="admin.courses.view"
                    filters={filters}
                />
            </div>
        </>
    );
}

CoursesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Courses',
            href: index(),
        },
    ],
};
