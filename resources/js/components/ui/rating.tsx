import { StarIcon } from 'lucide-react';

export function Rating({
    rating,
    size,
}: {
    rating?: number;
    size?: 'sm' | 'default' | 'lg';
}) {
    const fillPercent = rating !== undefined ? Math.min(Math.max(rating / 5, 0), 1) * 100 : 0;
    const STAR_SIZES = {
        sm: 12,
        default: 16,
        lg: 20,
    } as const;

    const starSize = STAR_SIZES[size ?? 'default'];

    return (
        <>
            {rating !== undefined && (
                <div
                    className="relative"
                    style={{ width: starSize, height: starSize }}
                >
                    <StarIcon
                        className="absolute inset-0 text-muted-foreground"
                        size={starSize}
                    />

                    <div
                        className="absolute inset-0 overflow-hidden"
                        style={{ width: `${fillPercent}%` }}
                    >
                        <StarIcon
                            className="fill-yellow-400 text-yellow-400"
                            size={starSize}
                        />
                    </div>
                </div>
            )}
        </>
    );
}
