// User types
export interface User {
    id: number;
    name: string;
    email: string;
    phone?: string;
    avatar?: string;
    provider?: 'google' | 'facebook' | 'zalo';
    status: 'active' | 'inactive' | 'banned';
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
    wallet?: Wallet;
}

export interface Wallet {
    id: number;
    user_id: number;
    balance: number;
    frozen_balance: number;
    created_at: string;
    updated_at: string;
}

export interface AuthResponse {
    user: User;
    token: string;
}

export interface LoginRequest {
    email: string;
    password: string;
}

export interface RegisterRequest {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    phone?: string;
}
