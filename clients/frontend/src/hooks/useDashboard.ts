'use client';

import { useState, useEffect } from 'react';
import { dashboardService, packageService } from '@/services';
import type { DashboardOverview, ActivityItem, CurrentPackage } from '@/types';

interface UseDashboardReturn {
    overview: DashboardOverview | null;
    activities: ActivityItem[];
    currentPackage: CurrentPackage | null;
    isLoading: boolean;
    error: string | null;
    refresh: () => Promise<void>;
}

export function useDashboard(): UseDashboardReturn {
    const [overview, setOverview] = useState<DashboardOverview | null>(null);
    const [activities, setActivities] = useState<ActivityItem[]>([]);
    const [currentPackage, setCurrentPackage] = useState<CurrentPackage | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const loadData = async () => {
        try {
            setIsLoading(true);
            setError(null);

            const [overviewRes, activitiesRes, packageRes] = await Promise.all([
                dashboardService.getOverview(),
                dashboardService.getRecentActivities(5),
                packageService.getCurrentPackage().catch(() => null),
            ]);

            if (overviewRes.data.success) {
                setOverview(overviewRes.data.data);
            }

            if (activitiesRes.data.success) {
                setActivities(activitiesRes.data.data || []);
            }

            if (packageRes?.data?.success && packageRes.data.data) {
                setCurrentPackage(packageRes.data.data);
            }
        } catch (err) {
            console.error('Dashboard error:', err);
            setError('Không thể tải dữ liệu dashboard');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        loadData();
    }, []);

    return {
        overview,
        activities,
        currentPackage,
        isLoading,
        error,
        refresh: loadData,
    };
}

export default useDashboard;
