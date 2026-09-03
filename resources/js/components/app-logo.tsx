import { usePage } from '@inertiajs/react';

/**
 * The app wordmark for the sidebar header — swapped by theme. In the collapsed
 * (icon) sidebar the parent clips it to its left edge.
 */
export default function AppLogo() {
    const { name } = usePage<{ name: string }>().props;

    return (
        <>
            <img
                src="/logo-sidebar-light.png"
                alt={name}
                className="h-8 w-auto max-w-none shrink-0 object-contain object-left dark:hidden"
            />
            <img
                src="/logo-sidebar-dark.png"
                alt={name}
                className="hidden h-8 w-auto max-w-none shrink-0 object-contain object-left dark:block"
            />
        </>
    );
}
