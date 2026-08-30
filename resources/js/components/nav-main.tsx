import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem, NavLeafItem } from '@/types';

type Section = {
    label: string | null;
    items: NavLeafItem[];
};

/**
 * Runs consecutive flat items into one unlabeled section; each NavGroupItem
 * becomes its own section labeled with the group's own title.
 */
function buildSections(items: NavItem[]): Section[] {
    const sections: Section[] = [];
    let leafRun: NavLeafItem[] = [];

    const flushLeafRun = () => {
        if (leafRun.length === 0) {
            return;
        }

        sections.push({ label: null, items: leafRun });
        leafRun = [];
    };

    for (const item of items) {
        if ('items' in item) {
            flushLeafRun();
            sections.push({ label: item.title, items: item.items });
        } else {
            leafRun.push(item);
        }
    }

    flushLeafRun();

    return sections;
}

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const { isCurrentUrl } = useCurrentUrl();
    const sections = buildSections(items);

    return (
        <>
            {sections.map((section, index) => (
                <SidebarGroup
                    key={section.label ?? `section-${index}`}
                    className="px-2 py-0"
                >
                    {section.label && (
                        <SidebarGroupLabel>{section.label}</SidebarGroupLabel>
                    )}
                    <SidebarMenu>
                        {section.items.map((item) => (
                            <SidebarMenuItem key={item.title}>
                                <SidebarMenuButton
                                    asChild
                                    isActive={isCurrentUrl(item.href)}
                                    tooltip={{ children: item.title }}
                                >
                                    <Link href={item.href} prefetch>
                                        {item.icon && <item.icon />}
                                        <span>{item.title}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        ))}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
