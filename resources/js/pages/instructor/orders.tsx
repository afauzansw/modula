import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/instructor/orders';

export default function InstructorOrders() {
    return (
        <>
            <Head title="Course Orders" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Course Orders"
                    description="Purchases of your courses"
                />
                <MockupTable
                    columns={[
                        'Student',
                        'Course',
                        'Amount',
                        'Status',
                        'Paid At',
                    ]}
                />
            </div>
        </>
    );
}

InstructorOrders.layout = {
    breadcrumbs: [
        {
            title: 'Course Orders',
            href: index(),
        },
    ],
};
