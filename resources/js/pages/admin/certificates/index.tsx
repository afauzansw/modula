import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/admin/certificates';

export default function CertificatesIndex() {
    return (
        <>
            <Head title="Certificate" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Certificate"
                    description="Certificates issued to students who completed a course"
                />
                <MockupTable
                    columns={[
                        'Student',
                        'Course',
                        'Certificate Number',
                        'Issued At',
                    ]}
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
