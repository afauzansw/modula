import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { Button } from '@/components/ui/button';
import type { Role } from '@/types';
import { RoleFormDialog } from './role-form-dialog';

/**
 * Edit + delete controls for one custom role. Renders nothing for system roles
 * (they can't be modified). Shared by the table's `actions` column and `RoleCard`.
 */
export function RoleActions({ role }: { role: Role }) {
    if (role.is_system) {
        return null;
    }

    return (
        <div className="flex justify-end gap-2">
            <RoleFormDialog
                role={role}
                trigger={
                    <Button variant="outline" size="sm">
                        Edit
                    </Button>
                }
            />
            <ConfirmDialog
                trigger={
                    <Button variant="destructive" size="sm">
                        Delete
                    </Button>
                }
                title={`Delete "${role.name}"?`}
                description="This permanently removes the role and revokes it from anyone holding it. This cannot be undone."
                form={RoleController.destroy.form(role.id)}
                confirmLabel="Delete role"
            />
        </div>
    );
}
