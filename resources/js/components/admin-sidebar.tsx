import { Link } from '@inertiajs/react';
import {
    Award,
    BookOpen,
    CreditCard,
    GraduationCap,
    KeyRound,
    LayoutGrid,
    Presentation,
    Settings,
    ShieldCheck,
    Tags,
    UserCog,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, settings } from '@/routes/admin';
import { index as adminsIndex } from '@/routes/admin/admins';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as certificatesIndex } from '@/routes/admin/certificates';
import { index as coursesIndex } from '@/routes/admin/courses';
import { index as instructorsIndex } from '@/routes/admin/instructors';
import { index as paymentsIndex } from '@/routes/admin/payments';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as studentsIndex } from '@/routes/admin/students';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Courses',
        href: coursesIndex(),
        icon: BookOpen,
    },
    {
        title: 'Course Category',
        href: categoriesIndex(),
        icon: Tags,
    },
    {
        title: 'Student Payment',
        href: paymentsIndex(),
        icon: CreditCard,
    },
    {
        title: 'Certificate',
        href: certificatesIndex(),
        icon: Award,
    },
    {
        title: 'User',
        icon: Users,
        items: [
            {
                title: 'Student',
                href: studentsIndex(),
                icon: GraduationCap,
            },
            {
                title: 'Instructor',
                href: instructorsIndex(),
                icon: Presentation,
            },
        ],
    },
    {
        title: 'Admin Management',
        icon: ShieldCheck,
        items: [
            {
                title: 'Admins',
                href: adminsIndex(),
                icon: UserCog,
            },
            {
                title: 'Role & Permission',
                href: rolesIndex(),
                icon: KeyRound,
            },
        ],
    },
    {
        title: 'App Setting',
        href: settings(),
        icon: Settings,
    },
];

export function AdminSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
