import { useHttp } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import CourseController from '@/actions/App/Http/Controllers/Instructor/CourseController';
import type { CategoryOption } from '@/types';

type Return = {
    categories: CategoryOption[];
    isLoading: boolean;
};

/**
 * Fetches every category (`{id, name}`) from `Instructor\CourseController::categories`
 * once on mount. Backs the Category select in the instructor courses filter card.
 */
export function useInstructorCourseCategories(): Return {
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
