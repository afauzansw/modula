import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function dateFormat(
    date?: string,
    options?: Intl.DateTimeFormatOptions,
): string {
    if (!date) {
        return '';
    }

    const d = new Date(date);
    return d.toLocaleDateString(
        undefined,
        options ?? {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        },
    );
}
