import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/admin/instructors';

export default function InstructorsIndex() {
    return (
        <>
            <Head title="Instructor" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Instructor"
                    description="Instructors and how many classes they teach"
                />
                <MockupTable columns={['Name', 'Email', 'Total Classes']} />
            </div>
        </>
    );
}

InstructorsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Instructor',
            href: index(),
        },
    ],
};
