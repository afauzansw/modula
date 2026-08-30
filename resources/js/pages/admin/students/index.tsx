import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/admin/students';

export default function StudentsIndex() {
    return (
        <>
            <Head title="Student" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Student"
                    description="Students and their enrolled classes by payment status"
                />
                <MockupTable
                    columns={[
                        'Name',
                        'Email',
                        'Paid Classes',
                        'Pending Classes',
                    ]}
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
