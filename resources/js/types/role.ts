export type Role = {
    id: number;
    name: string;
    is_system: boolean;
    permissions: string[];
};

/** Permission name => human label, from `AdminPermission::labels()`. */
export type PermissionCatalogue = Record<string, string>;
