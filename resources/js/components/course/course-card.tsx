import { Badge } from '@/components/ui/badge';
import { statusVariant } from '@/const/course';
import { CoursePrice } from './course-price';
import type { TCourse } from '@/types';

export function CourseCard({ course }: { course?: TCourse }) {
    return (
        <div className="flex h-full flex-col gap-2 rounded-lg border p-2.5">
            {course?.thumbnail ? (
                <img
                    src={course.thumbnail}
                    alt=""
                    className="mb-1 aspect-video w-full rounded object-cover"
                />
            ) : (
                <div className="mb-1 aspect-video w-full rounded bg-muted" />
            )}

            <div>
                <div className="flex items-start justify-between gap-3">
                    <span className="font-medium">{course?.title}</span>
                    <Badge
                        variant={statusVariant[course?.status ?? 'archived']}
                        className="shrink-0 capitalize"
                    >
                        {course?.status}
                    </Badge>
                </div>

                <p className="text-sm text-muted-foreground">
                    {course?.category?.name ?? ''} · {course?.instructor?.name ?? ''}
                </p>
            </div>

            <CoursePrice is_free={course?.is_free} price={course?.price} />
        </div>
    );
}
