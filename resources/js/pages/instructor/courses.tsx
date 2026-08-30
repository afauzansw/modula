import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/instructor/courses';

export default function InstructorCourses() {
    return (
        <>
            <Head title="Courses" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Courses"
                    description="Courses you author and manage"
                />
                <MockupTable
                    columns={[
                        'Title',
                        'Category',
                        'Students',
                        'Price',
                        'Status',
                    ]}
                />
            </div>
        </>
    );
}

InstructorCourses.layout = {
    breadcrumbs: [
        {
            title: 'Courses',
            href: index(),
        },
    ],
};
