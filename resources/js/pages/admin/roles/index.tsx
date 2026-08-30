import { Form, Head, Link } from '@inertiajs/react';
import RoleController from '@/actions/App/Http/Controllers/Admin/RoleController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
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
import { create, edit, index } from '@/routes/admin/roles';
import type { Paginated } from '@/types';

type RoleListItem = {
    id: number;
    name: string;
    is_system: boolean;
    permissions: string[];
};

type Props = {
    roles: Paginated<RoleListItem>;
};

export default function RolesIndex({ roles }: Props) {
    return (
        <>
            <Head title="Roles & Permissions" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Roles & Permissions"
                        description="Manage custom admin roles and the permissions they carry"
                    />
                    <Button asChild>
                        <Link href={create()}>New role</Link>
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50 text-left">
                            <tr>
                                <th className="px-4 py-2 font-medium">Name</th>
                                <th className="px-4 py-2 font-medium">Type</th>
                                <th className="px-4 py-2 font-medium">
                                    Permissions
                                </th>
                                <th className="px-4 py-2 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {roles.data.map((role) => (
                                <tr
                                    key={role.id}
                                    className="border-b last:border-0"
                                >
                                    <td className="px-4 py-3 font-medium">
                                        {role.name}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge
                                            variant={
                                                role.is_system
                                                    ? 'secondary'
                                                    : 'outline'
                                            }
                                        >
                                            {role.is_system
                                                ? 'System'
                                                : 'Custom'}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {role.permissions.length} permission
                                        {role.permissions.length === 1
                                            ? ''
                                            : 's'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {!role.is_system && (
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link href={edit(role.id)}>
                                                        Edit
                                                    </Link>
                                                </Button>
                                                <DeleteRoleDialog role={role} />
                                            </div>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {roles.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-6 text-center text-muted-foreground"
                                    >
                                        No roles yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {roles.last_page > 1 && (
                    <nav className="flex flex-wrap gap-1">
                        {roles.links.map((link, i) => (
                            <Button
                                key={i}
                                asChild={link.url !== null}
                                disabled={link.url === null}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                            >
                                {link.url !== null ? (
                                    <Link href={link.url} preserveScroll>
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    </Link>
                                ) : (
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                )}
                            </Button>
                        ))}
                    </nav>
                )}
            </div>
        </>
    );
}

function DeleteRoleDialog({ role }: { role: RoleListItem }) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="destructive" size="sm">
                    Delete
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Delete &quot;{role.name}&quot;?</DialogTitle>
                <DialogDescription>
                    This permanently removes the role and revokes it from anyone
                    holding it. This cannot be undone.
                </DialogDescription>

                <Form
                    {...RoleController.destroy.form(role.id)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                disabled={processing}
                                asChild
                            >
                                <button type="submit">Delete role</button>
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

RolesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Roles & Permissions',
            href: index(),
        },
    ],
};
