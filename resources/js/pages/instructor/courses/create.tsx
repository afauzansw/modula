import { Head } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/Instructor/CourseController';
import Heading from '@/components/heading';
import { index } from '@/routes/instructor/courses';
import type { CategoryOption } from '@/types';
import { CourseForm } from './components/course-form';

export default function CreateCourse({
    categories,
}: {
    categories: CategoryOption[];
}) {
    return (
        <>
            <Head title="New Course" />

            <div className="space-y-6 p-4">
                <Heading
                    title="New Course"
                    description="Set up a course. You can add modules and lessons after saving."
                />

                <CourseForm
                    categories={categories}
                    form={CourseController.store.form()}
                    submitLabel="Create course"
                />
            </div>
        </>
    );
}

CreateCourse.layout = {
    breadcrumbs: [
        { title: 'Courses', href: index() },
        { title: 'New course', href: CourseController.create.url() },
    ],
};
