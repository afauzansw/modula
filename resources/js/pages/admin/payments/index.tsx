import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import PaymentController from '@/actions/App/Http/Controllers/Admin/PaymentController';
import {
    DataTable,
    DataTableColumnHeader,
    useHttpDataTable,
} from '@/components/data-table';
import Heading from '@/components/heading';
import { index } from '@/routes/admin/payments';
import type { PaymentListItem } from '@/types';
import { formatAmount, formatPaidAt } from './lib/format';

/** Stable reference — maps sortable columns to their backend `sort` field. */
const sortFields = { amount: 'amount', paid_at: 'paid_at' };

const columns: ColumnDef<PaymentListItem>[] = [
    {
        accessorKey: 'student',
        enableSorting: false,
        header: 'Student',
        cell: ({ row }) => (
            <span className="font-medium">{row.original.student}</span>
        ),
    },
    {
        accessorKey: 'course',
        enableSorting: false,
        header: 'Course',
        cell: ({ row }) => (
            <span className="text-muted-foreground">{row.original.course}</span>
        ),
    },
    {
        accessorKey: 'order_number',
        enableSorting: false,
        header: 'Order #',
        cell: ({ row }) => (
            <code className="text-xs text-muted-foreground">
                {row.original.order_number}
            </code>
        ),
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Amount"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) => formatAmount(row.original.amount),
    },
    {
        accessorKey: 'method',
        enableSorting: false,
        header: 'Method',
        cell: ({ row }) => (
            <span className="text-muted-foreground capitalize">
                {row.original.method?.replace(/_/g, ' ') ?? '—'}
            </span>
        ),
    },
    {
        accessorKey: 'paid_at',
        header: ({ column }) => (
            <DataTableColumnHeader
                title="Paid At"
                canSort={column.getCanSort()}
                sorted={column.getIsSorted()}
                onToggleSort={column.getToggleSortingHandler()}
            />
        ),
        cell: ({ row }) => (
            <span className="text-muted-foreground">
                {formatPaidAt(row.original.paid_at)}
            </span>
        ),
    },
];

export default function PaymentsIndex() {
    const source = useHttpDataTable<PaymentListItem>({
        fetchUrl: PaymentController.fetch.url(),
        filterKey: 'student',
        sortFields,
    });

    return (
        <>
            <Head title="Student Payment" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Student Payment"
                    description="Review student payments for paid courses"
                />

                <DataTable
                    columns={columns}
                    source={source}
                    searchPlaceholder="Search by student…"
                    emptyMessage="No payments yet."
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
