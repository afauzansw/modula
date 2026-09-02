export type PaymentListItem = {
    id: number;
    student: string;
    course: string;
    order_number: string;
    amount: number;
    method: string | null;
    paid_at: string | null;
};
