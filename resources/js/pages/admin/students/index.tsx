import StudentController from '@/actions/App/Http/Controllers/Admin/StudentController';
import { DataTable, DataTableFilterDef, useHttpDataTable } from '@/components/data-table';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { dateFormat } from '@/lib/utils';
import { index } from '@/routes/admin/students';
import { UserListItem } from '@/types';
import { Head } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Book, BookOpen, BookOpenCheck, CheckCircle } from 'lucide-react';

const columns: ColumnDef<UserListItem>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => (
            <span className="font-medium">{row.original.name}</span>
        ),
    },
    {
        accessorKey: 'email',
        header: 'Email',
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
                variant={row.original.is_blocked ? 'destructive' : 'default'}
            >
                {row.original.is_blocked ? 'Blocked' : 'Active'}
            </Badge>
        ),
    },
    {
        accessorKey: 'created_at',
        header: 'Joined',
        cell: ({ row }) => dateFormat(row.original.created_at),
    },
    {
        accessorKey: 'created_at',
        header: 'Courses Stat',
        cell: ({ row }) => (
            <div className="flex gap-3">
                <div className="flex items-center gap-1">
                    <BookOpen className="h-3 w-3" />
                    <span className="text-xs font-medium text-muted-foreground">
                        1
                    </span>
                </div>
                <div className="flex items-center gap-1">
                    <CheckCircle className="h-3 w-3" />
                    <span className="text-xs font-medium text-muted-foreground">
                        1
                    </span>
                </div>
                <div className="flex items-center gap-1">
                    <CheckCircle className="h-3 w-3" />
                    <span className="text-xs font-medium text-muted-foreground">
                        1
                    </span>
                </div>
            </div>
        ),
    },
    {
        accessorKey: 'created_at',
        header: 'Last Login',
        cell: ({ row }) => dateFormat(row.original.created_at),
    },
];
export default function StudentsIndex() {
    const source = useHttpDataTable<UserListItem>({
        fetchUrl: StudentController.fetch.url(),
        filterKey: 'search',
    });

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
    ];

    return (
        <>
            <Head title="Students" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Students"
                    description="Manage the student directory"
                />

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search students…"
                    emptyMessage="No students yet."
                    views={['table']}
                    viewStorageKey="admin.students.view"
                    filters={filters}
                />
            </div>
        </>
    );
}

StudentsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Student',
            href: index(),
        },
    ],
};
