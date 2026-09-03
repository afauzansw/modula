import { useHttp } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import StudentController from '@/actions/App/Http/Controllers/Admin/StudentController';
import type { CertificateFilterOption, Paginated, UserListItem } from '@/types';

type Return = {
    students: CertificateFilterOption[];
    isLoading: boolean;
};

/**
 * Student `{id, name}` options for the certificates Student filter — reads the
 * existing `StudentController::fetch` listing (first page) once on mount.
 */
export function useCertificateStudents(): Return {
    const { get } = useHttp<Record<string, never>, Paginated<UserListItem>>({});

    const [students, setStudents] = useState<CertificateFilterOption[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let active = true;

        get(StudentController.fetch.url(), {
            onSuccess: (page) => {
                if (active) {
                    setStudents(
                        page.data.map((student) => ({
                            id: student.id,
                            name: student.name,
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

    return { students, isLoading };
}
