import { Form, Head } from '@inertiajs/react';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create, index } from '@/routes/admin/roles';

type Props = {
    permissions: Record<string, string>;
};

export default function CreateRole({ permissions }: Props) {
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
                                <div className="grid gap-2 rounded-lg border p-4">
                                    {Object.entries(permissions).map(
                                        ([value, label]) => (
                                            <div
                                                key={value}
                                                className="flex items-center gap-2"
                                            >
                                                <Checkbox
                                                    id={`permission-${value}`}
                                                    name="permissions[]"
                                                    value={value}
                                                />
                                                <Label
                                                    htmlFor={`permission-${value}`}
                                                    className="font-normal"
                                                >
                                                    {label}
                                                </Label>
                                            </div>
                                        ),
                                    )}
                                </div>
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
