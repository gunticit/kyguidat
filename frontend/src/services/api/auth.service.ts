import apiClient from './client';
import type { LoginRequest, RegisterRequest, AuthResponse } from '@/types';

export const authService = {
    login: (data: LoginRequest) =>
        apiClient.post<{ success: boolean; data: AuthResponse; message?: string }>('/auth/login', data),

    register: (data: RegisterRequest) =>
        apiClient.post<{ success: boolean; data: AuthResponse; message?: string }>('/auth/register', data),

    logout: () =>
        apiClient.post('/auth/logout'),

    getMe: () =>
        apiClient.get('/auth/me'),

    forgotPassword: (email: string) =>
        apiClient.post('/auth/forgot-password', { email }),

    resetPassword: (data: {
        email: string;
        token: string;
        password: string;
        password_confirmation: string
    }) => apiClient.post('/auth/reset-password', data),

    // Helper methods for local storage
    saveAuth: (token: string, user: AuthResponse['user']) => {
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
    },

    clearAuth: () => {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
    },

    getToken: (): string | null => {
        if (typeof window !== 'undefined') {
            return localStorage.getItem('auth_token');
        }
        return null;
    },

    getCurrentUser: (): AuthResponse['user'] | null => {
        if (typeof window !== 'undefined') {
            const user = localStorage.getItem('user');
            return user ? JSON.parse(user) : null;
        }
        return null;
    },

    isAuthenticated: (): boolean => {
        return !!authService.getToken();
    },
};

export default authService;
