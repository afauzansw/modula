import { CourseStatus } from "@/types";

export const statusVariant: Record<CourseStatus, 'default' | 'secondary' | 'outline'> =
    {
        published: 'default',
        draft: 'outline',
        archived: 'secondary',
    };