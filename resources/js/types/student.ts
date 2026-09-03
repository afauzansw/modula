export type MyCourseItem = {
    id: number;
    title: string;
    instructor: string;
    thumbnail: string | null;
    progress_percent: number;
    status: 'active' | 'completed';
};

export type MyCertificateItem = {
    id: number;
    course: string;
    certificate_number: string;
    issued_at: string;
};

export type OrderStatus = 'pending' | 'paid' | 'failed' | 'expired';

export type MyOrderItem = {
    id: number;
    course: string;
    order_number: string;
    amount: number;
    method: string | null;
    status: OrderStatus;
    paid_at: string | null;
};
