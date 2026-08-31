export type CourseStatus = 'draft' | 'published' | 'archived';

export type CourseListItem = {
    id: number;
    title: string;
    instructor: string;
    category: string | null;
    price: number;
    is_free: boolean;
    status: CourseStatus;
};

/** `{id, name}` for a category, from `CourseController::categories`. */
export type CategoryOption = {
    id: number;
    name: string;
};
