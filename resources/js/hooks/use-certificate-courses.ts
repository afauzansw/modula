import { useHttp } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import CourseController from '@/actions/App/Http/Controllers/Admin/CourseController';
import type {
    CertificateFilterOption,
    CourseListItem,
    Paginated,
} from '@/types';

type Return = {
    courses: CertificateFilterOption[];
    isLoading: boolean;
};

/**
 * Course `{id, name}` options for the certificates Course filter — reads the
 * existing `CourseController::fetch` listing (first page) once on mount.
 */
export function useCertificateCourses(): Return {
    const { get } = useHttp<Record<string, never>, Paginated<CourseListItem>>(
        {},
    );

    const [courses, setCourses] = useState<CertificateFilterOption[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let active = true;

        get(CourseController.fetch.url(), {
            onSuccess: (page) => {
                if (active) {
                    setCourses(
                        page.data.map((course) => ({
                            id: course.id,
                            name: course.title,
                        })),
                    );
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

    return { courses, isLoading };
}
