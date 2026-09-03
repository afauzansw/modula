import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/certificates';
import type { MyCertificateItem } from '@/types';

const date = new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' });

export default function StudentCertificates({
    certificates,
}: {
    certificates: MyCertificateItem[];
}) {
    return (
        <>
            <Head title="My Certificate" />

            <div className="space-y-6 p-4">
                <Heading
                    title="My Certificate"
                    description="Certificates you've earned by completing a course"
                />

                {certificates.length === 0 ? (
                    <div className="rounded-lg border p-8 text-center text-sm text-muted-foreground">
                        Complete a course to earn your first certificate.
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Course</TableHead>
                                    <TableHead>Certificate Number</TableHead>
                                    <TableHead>Issued At</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {certificates.map((certificate) => (
                                    <TableRow key={certificate.id}>
                                        <TableCell className="font-medium">
                                            {certificate.course}
                                        </TableCell>
                                        <TableCell>
                                            <code className="text-xs text-muted-foreground">
                                                {certificate.certificate_number}
                                            </code>
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {date.format(
                                                new Date(certificate.issued_at),
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                )}
            </div>
        </>
    );
}

StudentCertificates.layout = {
    breadcrumbs: [
        {
            title: 'My Certificate',
            href: index(),
        },
    ],
};
