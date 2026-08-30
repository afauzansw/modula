import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/admin/admins';

export default function AdminsIndex() {
    return (
        <>
            <Head title="Admins" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Admins"
                    description="Accounts with access to the admin panel"
                />
                <MockupTable columns={['Name', 'Email', 'Role']} />
            </div>
        </>
    );
}

AdminsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Admins',
            href: index(),
        },
    ],
};
