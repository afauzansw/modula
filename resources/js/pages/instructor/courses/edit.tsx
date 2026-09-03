import { Head } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/Instructor/CourseController';
import Heading from '@/components/heading';
import { index } from '@/routes/instructor/courses';
import type { CategoryOption, InstructorCourseFormValues } from '@/types';
import { CourseForm } from './components/course-form';

export default function EditCourse({
    course,
    categories,
}: {
    course: InstructorCourseFormValues;
    categories: CategoryOption[];
}) {
    return (
        <>
            <Head title={`Edit ${course.title}`} />

            <div className="space-y-6 p-4">
                <Heading
                    title={`Edit ${course.title}`}
                    description="Update the course details, pricing and publish status."
                />

                <CourseForm
                    categories={categories}
                    course={course}
                    form={CourseController.update.form(course.id)}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditCourse.layout = {
    breadcrumbs: [
        { title: 'Courses', href: index() },
        { title: 'Edit course', href: index() },
    ],
};
