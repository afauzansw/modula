import { Form, Head } from '@inertiajs/react';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { PermissionCheckboxList } from '@/components/roles/permission-checkbox-list';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePermissionCatalogue } from '@/hooks/use-permission-catalogue';
import { edit, index } from '@/routes/admin/roles';
import type { RoleFormData } from '@/types';

type Props = {
    role: RoleFormData;
};

export default function EditRole({ role }: Props) {
    const { permissions, isLoading } = usePermissionCatalogue();

    return (
        <>
            <Head title={`Edit ${role.name}`} />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title={`Edit ${role.name}`}
                    description="Rename this role and adjust which menus it can access"
                />

                <Form
                    {...RoleController.update.form(role.id)}
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
                                    defaultValue={role.name}
                                    placeholder="e.g. Support"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Permissions</Label>
                                <PermissionCheckboxList
                                    permissions={permissions}
                                    isLoading={isLoading}
                                    checkedValues={role.permissions}
                                />
                                <InputError message={errors.permissions} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="update-role-button"
                                >
                                    Save
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

EditRole.layout = (props: Props) => ({
    breadcrumbs: [
        {
            title: 'Roles & Permissions',
            href: index(),
        },
        {
            title: 'Edit role',
            href: edit(props.role.id),
        },
    ],
});
