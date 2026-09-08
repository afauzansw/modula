import { Avatar, AvatarFallback, AvatarImage } from './avatar';
import { useInitials } from '@/hooks/use-initials';
import { HoverCard, HoverCardContent, HoverCardTrigger } from './hover-card';
import { BookOpenIcon, GraduationCap, StarIcon } from 'lucide-react';
import { Rating } from './rating';

export function InstructorProfileAvatar({
    image,
    name,
    subtitle,
    rating,
    totalCourses,
    totalStudents,
}: {
    image: string;
    name: string;
    subtitle?: string;
    rating?: number;
    totalCourses?: number;
    totalStudents?: number;
}) {
    return (
        <HoverCard>
            <HoverCardTrigger>
                <ProfileAvatar
                    image={image}
                    name={name}
                    subtitle={subtitle}
                />
            </HoverCardTrigger>
            <HoverCardContent side="top">
                <div className="flex items-center justify-between gap-2">
                    <ProfileAvatar
                        image={image}
                        name={name}
                        subtitle={subtitle}
                    />
                    <Rating rating={rating} />
                </div>

                {totalCourses !== undefined ||
                    (totalStudents !== undefined && (
                        <div className="mt-3 flex items-center justify-between gap-1.5 border-t pt-3 text-xs text-muted-foreground">
                            <div className="flex items-center gap-1">
                                <BookOpenIcon className="h-3 w-3" />
                                <span>{totalCourses} courses</span>
                            </div>
                            <div className="flex items-center gap-1">
                                <GraduationCap className="h-3 w-3" />
                                <span>{totalStudents} students</span>
                            </div>
                        </div>
                    ))}
            </HoverCardContent>
        </HoverCard>
    );
}

export function ProfileAvatar({
    image,
    name,
    subtitle,
}: {
    image: string;
    name: string;
    subtitle?: string;
}) {
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
