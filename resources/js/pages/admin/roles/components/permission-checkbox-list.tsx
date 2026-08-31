import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import type { PermissionCatalogue } from '@/types';

type Props = {
    /** The catalogue, or `null` while it is still being fetched. */
    permissions: PermissionCatalogue | null;
    isLoading: boolean;
    /** Permission names to pre-check (edit form). */
    checkedValues?: string[];
};

/**
 * The `permissions[]` checkbox group on the role create/edit forms. Renders a
 * pulsing skeleton until `usePermissionCatalogue` resolves.
 */
export function PermissionCheckboxList({
    permissions,
    isLoading,
    checkedValues,
}: Props) {
    if (isLoading || permissions === null) {
        return (
            <div className="grid gap-3 rounded-lg border p-4">
                {Array.from({ length: 5 }).map((_, index) => (
                    <Skeleton key={index} className="h-5 w-40" />
                ))}
            </div>
        );
    }

    return (
        <div className="grid gap-2 rounded-lg border p-4">
            {Object.entries(permissions).map(([value, label]) => (
                <div key={value} className="flex items-center gap-2">
                    <Checkbox
                        id={`permission-${value}`}
                        name="permissions[]"
                        value={value}
                        defaultChecked={checkedValues?.includes(value)}
                    />
                    <Label
                        htmlFor={`permission-${value}`}
                        className="font-normal"
                    >
                        {label}
                    </Label>
                </div>
            ))}
        </div>
    );
}
