'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { FiPlus, FiMessageCircle, FiClock, FiCheckCircle, FiRefreshCw } from 'react-icons/fi';
import { supportApi } from '@/lib/api';
import styles from './support.module.css';

interface SupportTicket {
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    status: string;
    priority: string;
    created_at: string;
    updated_at: string;
    messages_count?: number;
}

interface TicketStats {
    open: number;
    in_progress: number;
    closed: number;
    total: number;
}

const categoryLabels: Record<string, string> = {
    general: 'Chung',
    payment: 'Thanh toán',
    consignment: 'Ký gửi',
    account: 'Tài khoản',
    other: 'Khác',
};

const getStatusBadge = (status: string) => {
    const statusMap: Record<string, { label: string; class: string; icon: React.ElementType }> = {
        open: { label: 'Đang mở', class: 'badge-pending', icon: FiMessageCircle },
        in_progress: { label: 'Đang xử lý', class: 'badge-info', icon: FiClock },
        waiting_reply: { label: 'Chờ phản hồi', class: 'badge-pending', icon: FiClock },
        resolved: { label: 'Đã giải quyết', class: 'badge-success', icon: FiCheckCircle },
        closed: { label: 'Đã đóng', class: 'badge-success', icon: FiCheckCircle },
    };
    return statusMap[status] || { label: status, class: 'badge-info', icon: FiMessageCircle };
};

const getPriorityBadge = (priority: string) => {
    const priorityMap: Record<string, { label: string; color: string }> = {
        low: { label: 'Thấp', color: '#22c55e' },
        medium: { label: 'Trung bình', color: '#f59e0b' },
        high: { label: 'Cao', color: '#ef4444' },
        urgent: { label: 'Khẩn cấp', color: '#dc2626' },
    };
    return priorityMap[priority] || { label: priority, color: '#94a3b8' };
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

export default function SupportPage() {
    const [tickets, setTickets] = useState<SupportTicket[]>([]);
    const [stats, setStats] = useState<TicketStats>({ open: 0, in_progress: 0, closed: 0, total: 0 });
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');

    useEffect(() => {
        loadTickets();
    }, [filter]);

    const loadTickets = async () => {
        try {
            setLoading(true);
            const params: { status?: string; page?: number } = {};
            if (filter !== 'all') {
                params.status = filter;
            }

            const response = await supportApi.getList(params);

            if (response.data.success) {
                const data = response.data.data.data || response.data.data;
                setTickets(Array.isArray(data) ? data : []);

                // Calculate stats
                if (response.data.data.stats) {
                    setStats(response.data.data.stats);
                } else {
                    // Calculate from data if stats not provided
                    const allTickets = Array.isArray(data) ? data : [];
                    setStats({
                        open: allTickets.filter((t: SupportTicket) => t.status === 'open' || t.status === 'waiting_reply').length,
                        in_progress: allTickets.filter((t: SupportTicket) => t.status === 'in_progress').length,
                        closed: allTickets.filter((t: SupportTicket) => t.status === 'closed' || t.status === 'resolved').length,
                        total: allTickets.length,
                    });
                }
            }
        } catch (error) {
            console.error('Error loading tickets:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading && tickets.length === 0) {
        return (
            <div className={styles.loading}>
                <div className={styles.spinner}></div>
                <p>Đang tải dữ liệu...</p>
            </div>
        );
    }

    return (
        <div>
            <div className={styles.header}>
                <div>
                    <h1 className={styles.pageTitle}>Hỗ trợ</h1>
                    <p className={styles.pageSubtitle}>Liên hệ Admin và theo dõi yêu cầu hỗ trợ</p>
                </div>
                <div className={styles.headerActions}>
                    <button
                        className={styles.refreshBtn}
                        onClick={loadTickets}
                        disabled={loading}
                    >
                        <FiRefreshCw className={loading ? styles.spinning : ''} />
                    </button>
                    <Link href="/dashboard/support/new" className="btn btn-primary">
                        <FiPlus /> Tạo yêu cầu mới
                    </Link>
                </div>
            </div>

            {/* Quick Stats */}
            <div className={styles.stats}>
                <div className={styles.statCard}>
                    <span className={styles.statNumber}>{stats.open + stats.in_progress}</span>
                    <span className={styles.statLabel}>Đang xử lý</span>
                </div>
                <div className={styles.statCard}>
                    <span className={styles.statNumber}>{stats.closed}</span>
                    <span className={styles.statLabel}>Đã giải quyết</span>
                </div>
                <div className={styles.statCard}>
                    <span className={styles.statNumber}>{stats.total}</span>
                    <span className={styles.statLabel}>Tổng cộng</span>
                </div>
            </div>

            {/* Filter Tabs */}
            <div className={styles.tabs}>
                {[
                    { value: 'all', label: 'Tất cả' },
                    { value: 'open', label: 'Đang mở' },
                    { value: 'in_progress', label: 'Đang xử lý' },
                    { value: 'closed', label: 'Đã đóng' },
                ].map(tab => (
                    <button
                        key={tab.value}
                        className={`${styles.tab} ${filter === tab.value ? styles.tabActive : ''}`}
                        onClick={() => setFilter(tab.value)}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            {/* Tickets List */}
            <div className={styles.ticketList}>
                {tickets.length === 0 ? (
                    <div className={styles.emptyState}>
                        <FiMessageCircle size={48} />
                        <p>Chưa có yêu cầu hỗ trợ nào</p>
                        <Link href="/dashboard/support/new" className="btn btn-primary">
                            <FiPlus /> Tạo yêu cầu đầu tiên
                        </Link>
                    </div>
                ) : (
                    tickets.map((ticket) => {
                        const status = getStatusBadge(ticket.status);
                        const priority = getPriorityBadge(ticket.priority);
                        return (
                            <Link
                                key={ticket.id}
                                href={`/dashboard/support/${ticket.id}`}
                                className={styles.ticketCard}
                            >
                                <div className={styles.ticketHeader}>
                                    <span className={styles.ticketNumber}>{ticket.ticket_number}</span>
                                    <span className={`badge ${status.class}`}>{status.label}</span>
                                </div>
                                <h3 className={styles.ticketSubject}>{ticket.subject}</h3>
                                <div className={styles.ticketMeta}>
                                    <span className={styles.category}>{categoryLabels[ticket.category] || ticket.category}</span>
                                    <span
                                        className={styles.priority}
                                        style={{ color: priority.color }}
                                    >
                                        {priority.label}
                                    </span>
                                    {ticket.messages_count !== undefined && (
                                        <span className={styles.messages}>
                                            <FiMessageCircle /> {ticket.messages_count}
                                        </span>
                                    )}
                                </div>
                                <div className={styles.ticketFooter}>
                                    <span>Tạo: {formatDate(ticket.created_at)}</span>
                                    <span>Cập nhật: {formatDate(ticket.updated_at)}</span>
                                </div>
                            </Link>
                        );
                    })
                )}
            </div>
        </div>
    );
}
