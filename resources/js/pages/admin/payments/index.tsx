import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { index } from '@/routes/admin/payments';

export default function PaymentsIndex() {
    return (
        <>
            <Head title="Student Payment" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Student Payment"
                    description="Review student payments for paid courses"
                />
                <MockupTable
                    columns={[
                        'Student',
                        'Course',
                        'Amount',
                        'Method',
                        'Paid At',
                    ]}
                />
            </div>
        </>
    );
}

PaymentsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Student Payment',
            href: index(),
        },
    ],
};
