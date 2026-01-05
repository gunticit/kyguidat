import apiClient from './client';
import type { User, ApiResponse } from '@/types';

export const userService = {
    getProfile: () =>
        apiClient.get<ApiResponse<User>>('/user/profile'),

    updateProfile: (data: { name?: string; phone?: string; avatar?: string }) =>
        apiClient.put<ApiResponse<User>>('/user/profile', data),

    updatePassword: (data: {
        current_password: string;
        new_password: string;
        new_password_confirmation: string
    }) => apiClient.put<ApiResponse<null>>('/user/password', data),
};

export default userService;
