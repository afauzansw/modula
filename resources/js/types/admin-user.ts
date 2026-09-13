export type AdminUserListItem = {
    id: number;
    name: string;
    email: string;
    roles: { name: string }[];
    created_at: string | null;
};
