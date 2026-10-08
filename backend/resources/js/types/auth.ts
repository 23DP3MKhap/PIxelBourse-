export type UserRole = 'user' | 'admin';

export type User = {
    id: number;
    username: string;
    email: string;
    coin_balance: number;
    role: UserRole;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
