import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { index } from '@/routes/courses';
import type { MyCourseItem } from '@/types';

export default function StudentCourses({
    courses,
}: {
    courses: MyCourseItem[];
}) {
    return (
        <>
            <Head title="My Courses" />

            <div className="space-y-6 p-4">
                <Heading
                    title="My Courses"
                    description="Courses you're enrolled in and your progress"
                />

                {courses.length === 0 ? (
                    <div className="rounded-lg border p-8 text-center text-sm text-muted-foreground">
                        You're not enrolled in any courses yet.
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {courses.map((course) => (
                            <CourseCard key={course.id} course={course} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

function CourseCard({ course }: { course: MyCourseItem }) {
    const completed = course.status === 'completed';

    return (
        <div className="flex flex-col overflow-hidden rounded-lg border">
            {course.thumbnail ? (
                <img
                    src={course.thumbnail}
                    alt=""
                    className="aspect-video w-full object-cover"
                />
            ) : (
                <div className="aspect-video w-full bg-muted" />
            )}

            <div className="flex flex-1 flex-col gap-2 p-4">
                <div className="flex items-start justify-between gap-2">
                    <span className="font-medium">{course.title}</span>
                    <Badge
                        variant={completed ? 'default' : 'secondary'}
                        className="shrink-0"
                    >
                        {completed ? 'Completed' : 'In progress'}
                    </Badge>
                </div>

                <p className="text-sm text-muted-foreground">
                    {course.instructor}
                </p>

                <div className="mt-auto space-y-1 pt-2">
                    <div className="flex justify-between text-xs text-muted-foreground">
                        <span>Progress</span>
                        <span>{course.progress_percent}%</span>
                    </div>
                    <div
                        className="h-2 overflow-hidden rounded-full bg-muted"
                        role="progressbar"
                        aria-valuenow={course.progress_percent}
                        aria-valuemin={0}
                        aria-valuemax={100}
                    >
                        <div
                            className="h-full rounded-full bg-primary transition-[width]"
                            style={{ width: `${course.progress_percent}%` }}
                        />
                    </div>
                </div>
            </div>
        </div>
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
