import { Link, usePage } from '@inertiajs/react';
import {
    Award,
    BookOpen,
    CreditCard,
    GraduationCap,
    LayoutGrid,
    ShieldCheck,
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
import { dashboard } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as certificatesIndex } from '@/routes/certificates';
import { index as coursesIndex } from '@/routes/courses';
import { dashboard as instructorDashboard } from '@/routes/instructor';
import { index as paymentsIndex } from '@/routes/payments';
import type { Auth, NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'My Courses',
            href: coursesIndex(),
            icon: BookOpen,
        },
        {
            title: 'My Payment',
            href: paymentsIndex(),
            icon: CreditCard,
        },
        {
            title: 'My Certificate',
            href: certificatesIndex(),
            icon: Award,
        },
    ];

    if (auth.permissions.includes('admin.dashboard')) {
        mainNavItems.push({
            title: 'Admin',
            href: adminDashboard(),
            icon: ShieldCheck,
        });
    }

    if (auth.roles.includes('instructor')) {
        mainNavItems.push({
            title: 'Instructor',
            href: instructorDashboard(),
            icon: GraduationCap,
        });
    }

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
