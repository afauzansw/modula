import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/certificates';

export default function StudentCertificates() {
    return (
        <>
            <Head title="My Certificate" />
            <div className="space-y-6 p-4">
                <Heading
                    title="My Certificate"
                    description="Certificates you've earned by completing a course"
                />
                <MockupTable
                    columns={['Course', 'Certificate Number', 'Issued At']}
                />
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
