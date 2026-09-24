import { Link } from '@inertiajs/react';
import CourseController from '@/actions/App/Http/Controllers/Instructor/CourseController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/instructor/courses';
import type { InstructorCourseListItem, TCourse } from '@/types';

/** Edit link + delete confirm for one course row. */
export function CourseActions({
    course,
}: {
    course: TCourse;
}) {
    return (
        <div className="flex justify-end gap-2">
            <Button variant="outline" size="sm" asChild>
                <Link href={edit(course.id)}>Edit</Link>
            </Button>
            <ConfirmDialog
                trigger={
                    <Button variant="destructive" size="sm">
                        Delete
                    </Button>
                }
                title={`Delete "${course.title}"?`}
                description="This permanently removes the course and everything under it — modules, lessons, enrollments. This cannot be undone."
                form={CourseController.destroy.form(course.id)}
                confirmLabel="Delete course"
            />
        </div>
    );
}
