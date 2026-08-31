import { Badge } from '@/components/ui/badge';
import type { Role } from '@/types';
import { RoleActions } from './role-actions';

/**
 * A role rendered as a card for the DataTable list/grid views. The header row is
 * left-padded to leave room for the selection checkbox `<DataTable>` overlays.
 */
export function RoleCard({ role }: { role: Role }) {
    const count = role.permissions.length;

    return (
        <div className="flex h-full flex-col gap-3 rounded-lg border p-4">
            <div className="flex items-start justify-between gap-3 pl-7">
                <span className="font-medium">{role.name}</span>
                <Badge variant={role.is_system ? 'secondary' : 'outline'}>
                    {role.is_system ? 'System' : 'Custom'}
                </Badge>
            </div>

            <p className="text-sm text-muted-foreground">
                {count} permission{count === 1 ? '' : 's'}
            </p>

            <div className="mt-auto">
                <RoleActions role={role} />
            </div>
        </div>
    );
}
