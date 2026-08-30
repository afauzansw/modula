import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/courses';

export default function StudentCourses() {
    return (
        <>
            <Head title="My Courses" />
            <div className="space-y-6 p-4">
                <Heading
                    title="My Courses"
                    description="Courses you're enrolled in and your progress"
                />
                <MockupTable
                    columns={['Course', 'Instructor', 'Progress', 'Status']}
                />
            </div>
        </>
    );
}

StudentCourses.layout = {
    breadcrumbs: [
        {
            title: 'My Courses',
            href: index(),
        },
    ],
};
