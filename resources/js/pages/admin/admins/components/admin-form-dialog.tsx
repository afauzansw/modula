import { Form } from '@inertiajs/react';
import { useState } from 'react';
import AdminUserController from '@/actions/App/Http/Controllers/Admin/AdminUserController';
import InputError from '@/components/input-error';
import { PermissionCheckboxList } from '@/components/permission-checkbox-list';
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
import type { AdminUserListItem } from '@/types';

type Props = {
    /** Element that opens the dialog (rendered via `DialogTrigger asChild`). */
    trigger: React.ReactNode;
    /** Omit to create; pass a table row to edit it. */
    admin?: AdminUserListItem;
};

/**
 * Create / edit an admin account in a modal. `admin` absent → `store`, present
 * → `update` (password optional). The permission checkboxes are the account's
 * direct admin-panel permissions.
 */
export function AdminFormDialog({ trigger, admin }: Props) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <AdminForm admin={admin} onSaved={() => setOpen(false)} />
            </DialogContent>
        </Dialog>
    );
}

function AdminForm({
    admin,
    onSaved,
}: {
    admin?: AdminUserListItem;
    onSaved: () => void;
}) {
    const { permissions, isLoading } = usePermissionCatalogue();

    return (
        <>
            <DialogTitle>
                {admin ? `Edit ${admin.name}` : 'New admin'}
            </DialogTitle>
            <DialogDescription>
                {admin
                    ? 'Update this admin account and the menus it can access.'
                    : 'Create an admin account and choose which menus it can access.'}
            </DialogDescription>

            <Form
                {...(admin
                    ? AdminUserController.update.form(admin.id)
                    : AdminUserController.store.form())}
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
                                defaultValue={admin?.name}
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                required
                                defaultValue={admin?.email}
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">
                                Password
                                {admin && (
                                    <span className="text-muted-foreground">
                                        {' '}
                                        — leave blank to keep
                                    </span>
                                )}
                            </Label>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                required={!admin}
                                autoComplete="new-password"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label>Permissions</Label>
                            <PermissionCheckboxList
                                permissions={permissions}
                                isLoading={isLoading}
                                checkedValues={admin?.permissions}
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
                                {admin ? 'Save' : 'Create admin'}
                            </Button>
                        </DialogFooter>
                    </>
                )}
            </Form>
        </>
    );
}
