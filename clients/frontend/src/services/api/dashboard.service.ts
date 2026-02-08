import apiClient from './client';
import type { DashboardOverview, DashboardStats, ActivityItem, ApiResponse } from '@/types';

export const dashboardService = {
    getOverview: () =>
        apiClient.get<ApiResponse<DashboardOverview>>('/dashboard'),

    getStats: () =>
        apiClient.get<ApiResponse<DashboardStats>>('/dashboard/stats'),

    getRecentActivities: (limit: number = 10) =>
        apiClient.get<ApiResponse<ActivityItem[]>>(`/dashboard/recent-activities?limit=${limit}`),
};

export default dashboardService;
