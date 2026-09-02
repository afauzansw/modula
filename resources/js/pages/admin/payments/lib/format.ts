const idr = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

const dateTime = new Intl.DateTimeFormat('en-GB', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

/** An amount as `Rp 149.000`. */
export function formatAmount(amount: number): string {
    return idr.format(amount);
}

/** An ISO timestamp as `12 Aug 2026, 14:30`, or `—` when null. */
export function formatPaidAt(iso: string | null): string {
    return iso ? dateTime.format(new Date(iso)) : '—';
}
