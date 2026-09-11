import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import CertificateController from '@/actions/App/Http/Controllers/Admin/CertificateController';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import type { DataTableFilterDef } from '@/components/data-table';
import Heading from '@/components/heading';
import { useCertificateCourses } from '@/hooks/use-certificate-courses';
import { useCertificateStudents } from '@/hooks/use-certificate-students';
import { index } from '@/routes/admin/certificates';
import type { TCertificate } from '@/types';
import { formatIssuedAt } from './lib/format';
import { CourseCell } from '@/components/course/course-cell';

/** Stable reference — maps sortable columns to their backend `sort` field. */
const sortFields = { issued_at: 'issued_at' };

const columns: ColumnDef<TCertificate>[] = [
    {
        accessorKey: 'student',
        header: 'Student',
        cell: ({ row }) => (
            <span className="font-medium">{row.original?.user?.name}</span>
        ),
    },
    {
        accessorKey: 'course',
        header: 'Course',
        cell: ({ row }) => (
            <CourseCell course={row.original?.course} subContent="category" />
        ),
    },
    {
        accessorKey: 'certificate_number',
        header: 'Certificate Number',
        cell: ({ row }) => (
            <code className="text-xs text-muted-foreground">
                {row.original.certificate_number}
            </code>
        ),
    },
    {
        accessorKey: 'issued_at',
        header: 'Issued At',
        cell: ({ row }) => (
            <span className="text-muted-foreground">
                {formatIssuedAt(row.original.issued_at)}
            </span>
        ),
    },
];

export default function CertificatesIndex() {
    const source = useHttpDataTable<TCertificate>({
        fetchUrl: CertificateController.fetch.url(),
        filterKey: 'certificate_number',
        sortFields,
    });

    const { students } = useCertificateStudents();
    const { courses } = useCertificateCourses();

    const filters: DataTableFilterDef[] = [
        {
            key: 'user_id',
            label: 'Student',
            type: 'select',
            options: students.map((student) => ({
                label: student.name,
                value: String(student.id),
            })),
        },
        {
            key: 'course_id',
            label: 'Course',
            type: 'select',
            options: courses.map((course) => ({
                label: course.name,
                value: String(course.id),
            })),
        },
    ];

    return (
        <>
            <Head title="Certificate" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Certificate"
                    description="Certificates issued to students who completed a course"
                />

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search by certificate number…"
                    emptyMessage="No certificates issued yet."
                    filters={filters}
                />
            </div>
        </>
    );
}

CertificatesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Certificate',
            href: index(),
        },
    ],
};
