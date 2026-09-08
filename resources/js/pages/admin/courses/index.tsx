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
import type { TCourse } from '@/types';
import { statusVariant } from '@/const/course';
import { CourseCard } from '@/components/course-card/course-card';
import { CoursePrice } from '@/components/course-card/course-price';
import { InstructorProfileAvatar } from '@/components/ui/profile-avatar';
import { Rating } from '@/components/ui/rating';
import { FileText, ListTree } from 'lucide-react';

const sortFields = { title: 'title', price: 'price' };

const columns: ColumnDef<TCourse>[] = [
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
            <div className="flex items-center gap-2">
                {row.original.thumbnail ? (
                    <img
                        src={row.original.thumbnail}
                        alt=""
                        className="h-9 w-14 shrink-0 rounded object-cover"
                    />
                ) : (
                    <div className="h-9 w-14 shrink-0 rounded bg-muted" />
                )}
                <div>
                    <span className="font-medium">{row.original.title}</span>
                    <div className="flex items-center gap-2.5 text-xs text-muted-foreground">
                        <div>
                            <ListTree className="inline-block h-3 w-3" />
                            <span className="ml-1">
                                {row.original?.lessons_count} lessons
                            </span>
                        </div>
                        <div>
                            <FileText className="inline-block h-3 w-3" />
                            <span className="ml-1">
                                {row.original?.modules_count} modules
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        ),
    },
    {
        accessorKey: 'instructor',
        enableSorting: false,
        header: 'Instructor',
        cell: ({ row }) => (
            <InstructorProfileAvatar
                image={''}
                name={row.original.instructor.name}
                subtitle={row.original.instructor.email}
            />
        ),
    },
    {
        accessorKey: 'category',
        enableSorting: false,
        header: 'Category',
        cell: ({ row }) => (
            <span className="text-muted-foreground">
                {row.original.category?.name ?? 'Uncategorized'}
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
        cell: ({ row }) => (
            <CoursePrice
                is_free={row.original.is_free}
                price={row.original.price}
            />
        ),
    },
    {
        accessorKey: 'ratings_avg_stars',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Rating"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) => <Rating rating={row.original?.ratings_avg_stars} />,
    },
    {
        accessorKey: 'status',
        enableSorting: false,
        header: 'Status',
        cell: ({ row }) => (
            <Badge
                variant={statusVariant[row.original.status]}
                className="shrink-0 capitalize"
            >
                {row.original.status}
            </Badge>
        ),
    },
];

export default function CoursesIndex() {
    const source = useHttpDataTable<TCourse>({
        fetchUrl:
            CourseController.fetch.url() +
            `?include=ratingAvg,lessonCount,moduleCount`,
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
