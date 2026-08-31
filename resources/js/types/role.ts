export type Role = {
    id: number;
    name: string;
    is_system: boolean;
    permissions: string[];
};

/** The subset the create/edit role form round-trips. */
export type RoleFormData = Pick<Role, 'id' | 'name' | 'permissions'>;

/** Permission name => human label, from `AdminPermission::labels()`. */
export type PermissionCatalogue = Record<string, string>;
