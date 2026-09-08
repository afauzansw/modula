export type CourseStatus = 'draft' | 'published' | 'archived';

export type TCourse = {
    id?: number;
    instructor_id: number;
    category_id: number | null;
    title: string;
    slug: string;
    description: string;
    price: number;
    is_free: boolean;
    status: CourseStatus;
    created_at?: string;
    updated_at?: string;

    category?: CategoryOption;
    instructor?: any;

    thumbnail?: string | null;
};

export type CategoryOption = {
    id?: number;
    name: string;
    slug: string;
    created_at?: string;
    updated_at?: string;
};
