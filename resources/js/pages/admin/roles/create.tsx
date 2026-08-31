import { Form, Head } from '@inertiajs/react';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { PermissionCheckboxList } from '@/components/roles/permission-checkbox-list';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePermissionCatalogue } from '@/hooks/use-permission-catalogue';
import { create, index } from '@/routes/admin/roles';

export default function CreateRole() {
    const { permissions, isLoading } = usePermissionCatalogue();

    return (
        <>
            <Head title="New role" />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title="New role"
                    description="Create a custom admin role and choose which menus it can access"
                />

                <Form {...RoleController.store.form()} className="space-y-6">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    autoFocus
                                    placeholder="e.g. Support"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Permissions</Label>
                                <PermissionCheckboxList
                                    permissions={permissions}
                                    isLoading={isLoading}
                                />
                                <InputError message={errors.permissions} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="create-role-button"
                                >
                                    Create role
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

CreateRole.layout = {
    breadcrumbs: [
        {
            title: 'Roles & Permissions',
            href: index(),
        },
        {
            title: 'New role',
            href: create(),
        },
    ],
};
