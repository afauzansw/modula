import type { CourseStatus } from './course';

export type InstructorCourseListItem = {
    id: number;
    title: string;
    category: string | null;
    price: number;
    is_free: boolean;
    status: CourseStatus;
    thumbnail: string | null;
};

/** The edit form's initial values, from `Instructor\CourseController::edit`. */
export type InstructorCourseFormValues = {
    id: number;
    title: string;
    category_id: number | null;
    description: string | null;
    price: number;
    is_free: boolean;
    status: CourseStatus;
    thumbnail: string | null;
};
