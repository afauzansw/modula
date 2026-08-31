const idr = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

/** "Free" for free courses, otherwise the price as `Rp 149.000`. */
export function formatPrice(price: number, isFree: boolean): string {
    return isFree ? 'Free' : idr.format(price);
}
