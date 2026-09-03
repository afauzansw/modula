import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/payments';
import type { MyOrderItem, OrderStatus } from '@/types';

const idr = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

const date = new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' });

const statusLabel: Record<OrderStatus, string> = {
    pending: 'Pending',
    paid: 'Paid',
    failed: 'Failed',
    expired: 'Expired',
};

const statusVariant: Record<
    OrderStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    pending: 'secondary',
    paid: 'default',
    failed: 'destructive',
    expired: 'outline',
};

export default function StudentPayments({ orders }: { orders: MyOrderItem[] }) {
    return (
        <>
            <Head title="My Payment" />

            <div className="space-y-6 p-4">
                <Heading
                    title="My Payment"
                    description="Your checkout history for paid courses"
                />

                {orders.length === 0 ? (
                    <div className="rounded-lg border p-8 text-center text-sm text-muted-foreground">
                        You haven't checked out any paid courses yet.
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Course</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Method</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Paid At</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {orders.map((order) => (
                                    <TableRow key={order.id}>
                                        <TableCell className="font-medium">
                                            {order.course}
                                        </TableCell>
                                        <TableCell>
                                            {idr.format(order.amount)}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground capitalize">
                                            {order.method?.replace(/_/g, ' ') ??
                                                '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    statusVariant[order.status]
                                                }
                                            >
                                                {statusLabel[order.status]}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {order.paid_at
                                                ? date.format(
                                                      new Date(order.paid_at),
                                                  )
                                                : '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                )}
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
