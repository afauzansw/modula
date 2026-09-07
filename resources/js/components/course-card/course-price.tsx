import type { CourseListItem } from '@/types';
import { cn } from '@/lib/utils';
import { formatPrice } from '@/pages/admin/courses/lib/format-price';

export function CoursePrice({ is_free, price }: { is_free: boolean; price: number }) {
    return (
        <p
            className={cn(
                'mt-auto text-sm font-medium',
                is_free && 'animate-pulse text-green-600',
            )}
        >
            {formatPrice(price, is_free)}
        </p>
    );
}
