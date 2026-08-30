import InstructorLayoutTemplate from '@/layouts/instructor/instructor-sidebar-layout';
import type { BreadcrumbItem } from '@/types';

export default function InstructorLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    return (
        <InstructorLayoutTemplate breadcrumbs={breadcrumbs}>
            {children}
        </InstructorLayoutTemplate>
    );
}
