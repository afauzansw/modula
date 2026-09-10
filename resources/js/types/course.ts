export type CourseStatus = 'draft' | 'published' | 'archived';

export type TCourse = {
    id: number;
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

    // Relations
    category?: CategoryOption;
    instructor?: any;

    // Included fields
    ratings_avg_stars?: number;
    modules_count?: number;
    lessons_count?: number;

    // Appended fields
    thumbnail?: string | null;
};

export type CategoryOption = {
    id: number;
    name: string;
    slug: string;
    created_at?: string;
    updated_at?: string;
};
