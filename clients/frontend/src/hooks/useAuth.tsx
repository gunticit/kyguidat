'use client';

import { useState, useEffect, createContext, useContext, ReactNode } from 'react';
import { useRouter } from 'next/navigation';
import { authService } from '@/services';
import type { User } from '@/types';
import { ROUTES } from '@/constants';

interface AuthContextType {
    user: User | null;
    isLoading: boolean;
    isAuthenticated: boolean;
    login: (email: string, password: string) => Promise<{ success: boolean; message?: string }>;
    register: (data: { name: string; email: string; password: string; password_confirmation: string; phone?: string }) => Promise<{ success: boolean; message?: string }>;
    logout: () => Promise<void>;
    refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
    const router = useRouter();
    const [user, setUser] = useState<User | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        // Check if user is logged in on mount
        const storedUser = authService.getCurrentUser();
        if (storedUser) {
            setUser(storedUser);
        }
        setIsLoading(false);
    }, []);

    const login = async (email: string, password: string) => {
        try {
            const response = await authService.login({ email, password });

            if (response.data.success) {
                const { token, user: userData } = response.data.data;
                authService.saveAuth(token, userData);
                setUser(userData);
                return { success: true };
            }

            return { success: false, message: response.data.message || 'Đăng nhập thất bại' };
        } catch (error: unknown) {
            const axiosError = error as { response?: { data?: { message?: string } } };
            return {
                success: false,
                message: axiosError.response?.data?.message || 'Có lỗi xảy ra'
            };
        }
    };

    const register = async (data: { name: string; email: string; password: string; password_confirmation: string; phone?: string }) => {
        try {
            const response = await authService.register(data);

            if (response.data.success) {
                const { token, user: userData } = response.data.data;
                authService.saveAuth(token, userData);
                setUser(userData);
                return { success: true };
            }

            return { success: false, message: response.data.message || 'Đăng ký thất bại' };
        } catch (error: unknown) {
            const axiosError = error as { response?: { data?: { message?: string } } };
            return {
                success: false,
                message: axiosError.response?.data?.message || 'Có lỗi xảy ra'
            };
        }
    };

    const logout = async () => {
        try {
            await authService.logout();
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            authService.clearAuth();
            setUser(null);
            router.push(ROUTES.login);
        }
    };

    const refreshUser = async () => {
        try {
            const response = await authService.getMe();
            if (response.data.success) {
                const userData = response.data.data;
                localStorage.setItem('user', JSON.stringify(userData));
                setUser(userData);
            }
        } catch (error) {
            console.error('Refresh user error:', error);
        }
    };

    return (
        <AuthContext.Provider
            value={{
                user,
                isLoading,
                isAuthenticated: !!user,
                login,
                register,
                logout,
                refreshUser,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}

export default useAuth;
