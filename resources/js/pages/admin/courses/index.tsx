import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/admin/courses';

export default function CoursesIndex() {
    return (
        <>
            <Head title="Courses" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Courses"
                    description="Manage the course catalog"
                />
                <MockupTable
                    columns={[
                        'Title',
                        'Instructor',
                        'Category',
                        'Price',
                        'Status',
                    ]}
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
