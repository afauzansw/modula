export type MyCourseItem = {
    id: number;
    title: string;
    instructor: string;
    thumbnail: string | null;
    progress_percent: number;
    status: 'active' | 'completed';
};
