import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavLeafItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
};

export type NavGroupItem = {
    title: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    items: NavLeafItem[];
};

export type NavItem = NavLeafItem | NavGroupItem;
