export type AdminUserListItem = {
    id: number;
    name: string;
    email: string;
    permissions: string[];
    created_at: string | null;
};
