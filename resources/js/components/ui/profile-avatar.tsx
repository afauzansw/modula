import { Avatar, AvatarFallback, AvatarImage } from './avatar';
import { useInitials } from '@/hooks/use-initials';

export function ProfileAvatar({ image, name, subtitle }: { image: string; name: string; subtitle?: string }) {
    const getInitials = useInitials();

    return (
        <div className="flex items-center gap-2">
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarImage src={image} alt={name} />
                <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                    {getInitials(name)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate">{name}</span>
                {subtitle && (
                    <span className="truncate text-xs text-muted-foreground">
                        {subtitle}
                    </span>
                )}
            </div>
        </div>
    );
}
