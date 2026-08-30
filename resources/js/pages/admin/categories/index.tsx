import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/admin/categories';

export default function CategoriesIndex() {
    return (
        <>
            <Head title="Course Category" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Course Category"
                    description="Manage the categories courses are organized under"
                />
                <MockupTable columns={['Name', 'Slug', 'Courses']} />
            </div>
        </>
    );
}

CategoriesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Course Category',
            href: index(),
        },
    ],
};
