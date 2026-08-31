import { useHttp } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import type { PermissionCatalogue } from '@/types';

type UsePermissionCatalogueReturn = {
    /** `null` until the first response lands. */
    permissions: PermissionCatalogue | null;
    isLoading: boolean;
};

/**
 * Fetches the admin-permission catalogue (name => label) from
 * `RoleController::permissions` once on mount. Backs the checkbox list on the
 * role create/edit forms.
 */
export function usePermissionCatalogue(): UsePermissionCatalogueReturn {
    const { get } = useHttp<Record<string, never>, PermissionCatalogue>({});

    const [permissions, setPermissions] = useState<PermissionCatalogue | null>(
        null,
    );
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let active = true;

        get(RoleController.permissions.url(), {
            onSuccess: (data) => {
                if (active) {
                    setPermissions(data);
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

    return { permissions, isLoading };
}
