import type { TCourse } from '@/types';
import { FileText, ListTree } from 'lucide-react';

export function CourseCell({
    course,
    subContent,
}: {
    course?: TCourse;
    subContent?: 'category' | 'detail';
}) {
    return (
        <div className="flex items-center gap-2">
            {course?.thumbnail ? (
                <img
                    src={course.thumbnail}
                    alt=""
                    className="h-9 w-14 shrink-0 rounded object-cover"
                />
            ) : (
                <div className="h-9 w-14 shrink-0 rounded bg-muted" />
            )}
            <div>
                <span className="font-medium">{course?.title}</span>
                {subContent === 'category' ? (
                    <div className="text-xs text-muted-foreground">
                        {course?.category?.name ?? 'Uncategorized'}
                    </div>
                ) : (
                    subContent === 'detail' && (
                        <div className="flex items-center gap-2.5 text-xs text-muted-foreground">
                            <div>
                                <ListTree className="inline-block h-3 w-3" />
                                <span className="ml-1">
                                    {course?.lessons_count} lessons
                                </span>
                            </div>
                            <div>
                                <FileText className="inline-block h-3 w-3" />
                                <span className="ml-1">
                                    {course?.modules_count} modules
                                </span>
                            </div>
                        </div>
                    )
                )}
            </div>
        </div>
    );
}
