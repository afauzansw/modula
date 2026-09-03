import AdminUserController from '@/actions/App/Http/Controllers/Admin/AdminUserController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { Button } from '@/components/ui/button';
import type { AdminUserListItem } from '@/types';
import { AdminFormDialog } from './admin-form-dialog';

/** Edit + delete controls for one admin row. */
export function AdminActions({ admin }: { admin: AdminUserListItem }) {
    return (
        <div className="flex justify-end gap-2">
            <AdminFormDialog
                admin={admin}
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
                title={`Delete ${admin.name}?`}
                description="This permanently removes the account and its admin-panel access. This cannot be undone."
                form={AdminUserController.destroy.form(admin.id)}
                confirmLabel="Delete admin"
            />
        </div>
    );
}
