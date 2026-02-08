'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { FiPackage, FiDollarSign, FiTrendingUp, FiClock, FiMessageSquare, FiCreditCard } from 'react-icons/fi';
import { dashboardApi, postingPackageApi } from '@/lib/api';
import styles from './page.module.css';

interface DashboardOverview {
    wallet: {
        balance: number;
        frozen_balance: number;
    };
    consignments: {
        total: number;
        pending: number;
        selling: number;
        sold: number;
    };
    payments: {
        total_deposited: number;
        pending: number;
    };
    support: {
        open_tickets: number;
    };
}

interface Activity {
    type: 'consignment' | 'payment' | 'support';
    title: string;
    status: string;
    created_at: string;
}

interface CurrentPackage {
    package_name: string;
    remaining_days: number;
    remaining_posts: number | string;
}

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
};

const formatTimeAgo = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffMins < 1) return 'Vừa xong';
    if (diffMins < 60) return `${diffMins} phút trước`;
    if (diffHours < 24) return `${diffHours} giờ trước`;
    if (diffDays < 7) return `${diffDays} ngày trước`;
    return date.toLocaleDateString('vi-VN');
};

const getStatusBadge = (status: string) => {
    const statusMap: Record<string, { label: string; class: string }> = {
        pending: { label: 'Chờ duyệt', class: 'badge-pending' },
        completed: { label: 'Hoàn thành', class: 'badge-success' },
        selling: { label: 'Đang bán', class: 'badge-info' },
        sold: { label: 'Đã bán', class: 'badge-success' },
        open: { label: 'Đang mở', class: 'badge-pending' },
        in_progress: { label: 'Đang xử lý', class: 'badge-info' },
        waiting_reply: { label: 'Chờ phản hồi', class: 'badge-pending' },
        closed: { label: 'Đã đóng', class: 'badge-secondary' },
        cancelled: { label: 'Đã hủy', class: 'badge-error' },
        failed: { label: 'Thất bại', class: 'badge-error' },
    };
    return statusMap[status] || { label: status, class: 'badge-info' };
};

export default function DashboardPage() {
    const [loading, setLoading] = useState(true);
    const [overview, setOverview] = useState<DashboardOverview | null>(null);
    const [activities, setActivities] = useState<Activity[]>([]);
    const [currentPackage, setCurrentPackage] = useState<CurrentPackage | null>(null);
    const [userName, setUserName] = useState('');

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async () => {
        try {
            setLoading(true);

            // Get user name from localStorage
            const userStr = localStorage.getItem('user');
            if (userStr) {
                const user = JSON.parse(userStr);
                setUserName(user.name || 'bạn');
            }

            // Load dashboard overview
            const [overviewRes, activitiesRes, packageRes] = await Promise.all([
                dashboardApi.getOverview(),
                dashboardApi.getRecentActivities(5),
                postingPackageApi.getCurrentPackage().catch(() => null),
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
        } catch (error) {
            console.error('Error loading dashboard:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className={styles.loading}>
                <div className={styles.spinner}></div>
                <p>Đang tải dữ liệu...</p>
            </div>
        );
    }

    const stats = overview ? [
        {
            label: 'Số dư ví',
            value: formatCurrency(overview.wallet.balance),
            icon: FiDollarSign,
            color: '#22c55e',
            link: '/dashboard/deposit'
        },
        {
            label: 'Đang ký gửi',
            value: String(overview.consignments.selling),
            icon: FiPackage,
            color: '#6366f1',
            link: '/dashboard/consignments'
        },
        {
            label: 'Đã bán',
            value: String(overview.consignments.sold),
            icon: FiTrendingUp,
            color: '#f59e0b',
            link: '/dashboard/consignments'
        },
        {
            label: 'Chờ xử lý',
            value: String(overview.consignments.pending + overview.payments.pending),
            icon: FiClock,
            color: '#8b5cf6',
            link: '/dashboard/consignments'
        },
    ] : [];

    return (
        <div>
            <h1 className={styles.pageTitle}>Tổng quan</h1>
            <p className={styles.pageSubtitle}>
                Xin chào, <strong>{userName}</strong>! Đây là tổng quan hoạt động của bạn.
            </p>

            {/* Current Package Banner */}
            {currentPackage && (
                <div className={styles.packageBanner}>
                    <div className={styles.packageInfo}>
                        <FiCreditCard size={24} />
                        <div>
                            <strong>{currentPackage.package_name}</strong>
                            <span>Còn {currentPackage.remaining_days} ngày • {currentPackage.remaining_posts} bài đăng</span>
                        </div>
                    </div>
                    <Link href="/dashboard/packages" className="btn btn-sm">
                        Gia hạn
                    </Link>
                </div>
            )}

            {!currentPackage && (
                <div className={styles.packageBannerEmpty}>
                    <div className={styles.packageInfo}>
                        <FiCreditCard size={24} />
                        <div>
                            <strong>Chưa có gói đăng bài</strong>
                            <span>Mua gói để bắt đầu đăng bài ký gửi</span>
                        </div>
                    </div>
                    <Link href="/dashboard/packages" className="btn btn-primary btn-sm">
                        Mua gói ngay
                    </Link>
                </div>
            )}

            {/* Stats Grid */}
            <div className={styles.statsGrid}>
                {stats.map((stat, index) => (
                    <Link key={index} href={stat.link} className={`card ${styles.statCard}`}>
                        <div className={styles.statIcon} style={{ background: `${stat.color}20` }}>
                            <stat.icon size={24} color={stat.color} />
                        </div>
                        <div className={styles.statInfo}>
                            <p className={styles.statLabel}>{stat.label}</p>
                            <h3 className={styles.statValue}>{stat.value}</h3>
                        </div>
                    </Link>
                ))}
            </div>

            {/* Recent Activities */}
            <div className={styles.section}>
                <h2 className={styles.sectionTitle}>Hoạt động gần đây</h2>
                <div className={styles.activityList}>
                    {activities.length === 0 ? (
                        <div className={styles.emptyState}>
                            <p>Chưa có hoạt động nào</p>
                        </div>
                    ) : (
                        activities.map((activity, index) => {
                            const status = getStatusBadge(activity.status);
                            return (
                                <div key={index} className={styles.activityItem}>
                                    <div className={styles.activityIcon}>
                                        {activity.type === 'consignment' && <FiPackage />}
                                        {activity.type === 'payment' && <FiDollarSign />}
                                        {activity.type === 'support' && <FiMessageSquare />}
                                    </div>
                                    <div className={styles.activityInfo}>
                                        <h4 className={styles.activityTitle}>{activity.title}</h4>
                                        <span className={styles.activityTime}>
                                            {formatTimeAgo(activity.created_at)}
                                        </span>
                                    </div>
                                    <span className={`badge ${status.class}`}>{status.label}</span>
                                </div>
                            );
                        })
                    )}
                </div>
            </div>

            {/* Quick Actions */}
            <div className={styles.section}>
                <h2 className={styles.sectionTitle}>Thao tác nhanh</h2>
                <div className={styles.quickActions}>
                    <Link href="/dashboard/consignments/new" className="btn btn-primary">
                        <FiPackage /> Tạo ký gửi mới
                    </Link>
                    <Link href="/dashboard/deposit" className="btn btn-success">
                        <FiDollarSign /> Nạp tiền
                    </Link>
                    <Link href="/dashboard/packages" className="btn btn-secondary">
                        <FiCreditCard /> Mua gói đăng bài
                    </Link>
                    <Link href="/dashboard/support" className="btn btn-outline">
                        <FiMessageSquare /> Liên hệ hỗ trợ
                    </Link>
                </div>
            </div>

            {/* Support Tickets Summary */}
            {overview && overview.support.open_tickets > 0 && (
                <div className={styles.section}>
                    <div className={styles.supportNotice}>
                        <FiMessageSquare size={20} />
                        <span>
                            Bạn có <strong>{overview.support.open_tickets}</strong> yêu cầu hỗ trợ đang mở
                        </span>
                        <Link href="/dashboard/support" className="btn btn-sm">
                            Xem chi tiết
                        </Link>
                    </div>
                </div>
            )}
        </div>
    );
}
