import { Form } from '@inertiajs/react';
import { useState } from 'react';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePermissionCatalogue } from '@/hooks/use-permission-catalogue';
import type { Role } from '@/types';
import { PermissionCheckboxList } from './permission-checkbox-list';

type Props = {
    /** Element that opens the dialog (rendered via `DialogTrigger asChild`). */
    trigger: React.ReactNode;
    /** Omit to create a new role; pass a table row's role to edit it. */
    role?: Role;
};

/**
 * Create / edit a custom role in a modal. `role` absent → create (`store`);
 * `role` present → edit (`update`). Replaces the former standalone
 * `admin/roles/create` and `admin/roles/edit` pages.
 */
export function RoleFormDialog({ trigger, role }: Props) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                {/* Rendered only while open, so `usePermissionCatalogue` fetches
                    on demand rather than once per row the table renders. */}
                <RoleForm role={role} onSaved={() => setOpen(false)} />
            </DialogContent>
        </Dialog>
    );
}

function RoleForm({ role, onSaved }: { role?: Role; onSaved: () => void }) {
    const { permissions, isLoading } = usePermissionCatalogue();

    return (
        <>
            <DialogTitle>{role ? `Edit ${role.name}` : 'New role'}</DialogTitle>
            <DialogDescription>
                {role
                    ? 'Rename this role and adjust which menus it can access.'
                    : 'Create a custom admin role and choose which menus it can access.'}
            </DialogDescription>

            <Form
                {...(role
                    ? RoleController.update.form(role.id)
                    : RoleController.store.form())}
                options={{ preserveScroll: true }}
                onSuccess={onSaved}
                className="space-y-6"
            >
                {({ errors, processing }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="name">Name</Label>
                            <Input
                                id="name"
                                name="name"
                                required
                                autoFocus
                                defaultValue={role?.name}
                                placeholder="e.g. Support"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label>Permissions</Label>
                            <PermissionCheckboxList
                                permissions={permissions}
                                isLoading={isLoading}
                                checkedValues={role?.permissions}
                            />
                            <InputError message={errors.permissions} />
                        </div>

                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary" type="button">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button type="submit" disabled={processing}>
                                {role ? 'Save' : 'Create role'}
                            </Button>
                        </DialogFooter>
                    </>
                )}
            </Form>
        </>
    );
}
