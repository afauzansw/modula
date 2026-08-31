import { useHttp } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import type { CategoryOption } from '@/types';

type UseCourseCategoriesReturn = {
    categories: CategoryOption[];
    isLoading: boolean;
};

/**
 * Fetches every category (`{id, name}`) from `CourseController::categories` once
 * on mount. Backs the Category select in the courses filter card.
 */
export function useCourseCategories(): UseCourseCategoriesReturn {
    const { get } = useHttp<Record<string, never>, CategoryOption[]>({});

    const [categories, setCategories] = useState<CategoryOption[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let active = true;

        get(CourseController.categories.url(), {
            onSuccess: (data) => {
                if (active) {
                    setCategories(data);
                }
            },
            onFinish: () => {
                if (active) {
                    setIsLoading(false);
                }
            },
        });

        return () => {
            active = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return { categories, isLoading };
}
