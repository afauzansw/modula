import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/payments';

export default function StudentPayments() {
    return (
        <>
            <Head title="My Payment" />
            <div className="space-y-6 p-4">
                <Heading
                    title="My Payment"
                    description="Your checkout history for paid courses"
                />
                <MockupTable
                    columns={[
                        'Course',
                        'Amount',
                        'Method',
                        'Status',
                        'Paid At',
                    ]}
                />
            </div>
        </>
    );
}

StudentPayments.layout = {
    breadcrumbs: [
        {
            title: 'My Payment',
            href: index(),
        },
    ],
};
