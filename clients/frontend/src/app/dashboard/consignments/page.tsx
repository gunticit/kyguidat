'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { FiPlus, FiSearch, FiFilter, FiEye, FiEdit, FiTrash2, FiX, FiRefreshCw } from 'react-icons/fi';
import { consignmentApi } from '@/lib/api';
import { formatCurrency } from '@/lib/formatCurrency';
import styles from './consignments.module.css';

interface Consignment {
    id: number;
    code: string;
    title: string;
    description?: string;
    address: string;
    google_map_link?: string;
    price: number;
    min_price?: number;
    seller_phone: string;
    status: string;
    reject_reason?: string;
    created_at: string;
    images?: string[];
    description_files?: string[];
    note_to_admin?: string;
}

interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const statusOptions = [
    { value: '', label: 'Tất cả' },
    { value: 'pending', label: 'Chờ duyệt' },
    { value: 'approved', label: 'Đã duyệt' },
    { value: 'rejected', label: 'Từ chối' },
    { value: 'selling', label: 'Đang bán' },
    { value: 'sold', label: 'Đã bán' },
    { value: 'cancelled', label: 'Đã hủy' },
    { value: 'deactivated', label: 'Đã tắt tự động' },
];

const getStatusBadge = (status: string) => {
    const statusMap: Record<string, { label: string; class: string }> = {
        pending: { label: 'Chờ duyệt', class: 'badge-pending' },
        approved: { label: 'Đã duyệt', class: 'badge-info' },
        rejected: { label: 'Từ chối', class: 'badge-error' },
        selling: { label: 'Đang bán', class: 'badge-success' },
        sold: { label: 'Đã bán', class: 'badge-success' },
        cancelled: { label: 'Đã hủy', class: 'badge-error' },
        deactivated: { label: 'Đã tắt', class: 'badge-warning' },
    };
    return statusMap[status] || { label: status, class: 'badge-info' };
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('vi-VN');
};

export default function ConsignmentsPage() {
    const [consignments, setConsignments] = useState<Consignment[]>([]);
    const [pagination, setPagination] = useState<PaginationData | null>(null);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [deleteConfirm, setDeleteConfirm] = useState<number | null>(null);
    const [deleting, setDeleting] = useState(false);
    const [reactivating, setReactivating] = useState<number | null>(null);

    useEffect(() => {
        loadConsignments();
    }, [statusFilter, currentPage]);

    // Debounce search
    useEffect(() => {
        const timer = setTimeout(() => {
            setCurrentPage(1);
            loadConsignments();
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    const loadConsignments = async () => {
        try {
            setLoading(true);
            const response = await consignmentApi.getList({
                status: statusFilter || undefined,
                search: search || undefined,
                page: currentPage,
            });

            if (response.data.success) {
                setConsignments(response.data.data.data || response.data.data);
                if (response.data.data.meta) {
                    setPagination(response.data.data.meta);
                } else if (response.data.data.current_page) {
                    setPagination({
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                    });
                }
            }
        } catch (error) {
            console.error('Error loading consignments:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id: number) => {
        try {
            setDeleting(true);
            await consignmentApi.delete(id);
            setDeleteConfirm(null);
            loadConsignments();
        } catch (error) {
            console.error('Error deleting consignment:', error);
            alert('Không thể xóa ký gửi này');
        } finally {
            setDeleting(false);
        }
    };

    const handleReactivate = async (id: number) => {
        try {
            setReactivating(id);
            const response = await consignmentApi.reactivate(id);
            if (response.data.success) {
                loadConsignments();
            } else {
                alert(response.data.message || 'Không thể mở lại');
            }
        } catch (error) {
            console.error('Error reactivating:', error);
            alert('Có lỗi xảy ra khi mở lại sản phẩm');
        } finally {
            setReactivating(null);
        }
    };

    if (loading && consignments.length === 0) {
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
                    <h1 className={styles.pageTitle}>Ký gửi</h1>
                    <p className={styles.pageSubtitle}>Quản lý các yêu cầu ký gửi của bạn</p>
                </div>
                <Link href="/dashboard/consignments/new" className="btn btn-primary">
                    <FiPlus /> Tạo mới
                </Link>
            </div>

            {/* Filters */}
            <div className={styles.filters}>
                <div className={styles.searchBox}>
                    <FiSearch className={styles.searchIcon} />
                    <input
                        type="text"
                        className="input"
                        placeholder="Tìm kiếm theo mã hoặc tên..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        style={{ paddingLeft: '44px' }}
                    />
                </div>
                <div className={styles.filterSelect}>
                    <FiFilter className={styles.filterIcon} />
                    <select
                        className="input"
                        value={statusFilter}
                        onChange={(e) => {
                            setStatusFilter(e.target.value);
                            setCurrentPage(1);
                        }}
                        style={{ paddingLeft: '44px' }}
                    >
                        {statusOptions.map(opt => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Table */}
            <div className={styles.tableWrapper}>
                {consignments.length === 0 ? (
                    <div className={styles.emptyState}>
                        <p>Chưa có yêu cầu ký gửi nào</p>
                        <Link href="/dashboard/consignments/new" className="btn btn-primary">
                            <FiPlus /> Tạo ký gửi đầu tiên
                        </Link>
                    </div>
                ) : (
                    <table className={styles.table}>
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tiêu đề</th>
                                <th>Địa chỉ</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {consignments.map((item) => {
                                const status = getStatusBadge(item.status);
                                return (
                                    <tr key={item.id}>
                                        <td className={styles.code}>{item.code}</td>
                                        <td className={styles.titleCell}>{item.title}</td>
                                        <td className={styles.addressCell}>{item.address}</td>
                                        <td className={styles.priceCell}>{formatCurrency(item.price, { showBillion: true })}</td>
                                        <td>
                                            <span className={`badge ${status.class}`}>{status.label}</span>
                                            {item.status === 'rejected' && item.reject_reason && (
                                                <div style={{ fontSize: '12px', color: '#ef4444', marginTop: '4px' }}>
                                                    Lý do: {item.reject_reason}
                                                </div>
                                            )}
                                        </td>
                                        <td>{formatDate(item.created_at)}</td>
                                        <td>
                                            <div className={styles.actions}>
                                                <Link
                                                    href={`/dashboard/consignments/${item.id}`}
                                                    className={styles.actionBtn}
                                                    title="Xem chi tiết"
                                                >
                                                    <FiEye />
                                                </Link>
                                                {item.status === 'pending' && (
                                                    <>
                                                        <Link
                                                            href={`/dashboard/consignments/${item.id}/edit`}
                                                            className={styles.actionBtn}
                                                            title="Chỉnh sửa"
                                                        >
                                                            <FiEdit />
                                                        </Link>
                                                        <button
                                                            className={`${styles.actionBtn} ${styles.deleteBtn}`}
                                                            title="Xóa"
                                                            onClick={() => setDeleteConfirm(item.id)}
                                                        >
                                                            <FiTrash2 />
                                                        </button>
                                                    </>
                                                )}
                                                {item.status === 'deactivated' && (
                                                    <button
                                                        className={`${styles.actionBtn}`}
                                                        title="Mở lại"
                                                        onClick={() => handleReactivate(item.id)}
                                                        disabled={reactivating === item.id}
                                                        style={{ color: '#22c55e' }}
                                                    >
                                                        <FiRefreshCw className={reactivating === item.id ? styles.spinning : ''} />
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Pagination */}
            {pagination && pagination.last_page > 1 && (
                <div className={styles.pagination}>
                    <button
                        className="btn btn-secondary"
                        disabled={currentPage === 1}
                        onClick={() => setCurrentPage(p => p - 1)}
                    >
                        Trước
                    </button>
                    <span className={styles.pageInfo}>
                        Trang {pagination.current_page} / {pagination.last_page}
                    </span>
                    <button
                        className="btn btn-secondary"
                        disabled={currentPage === pagination.last_page}
                        onClick={() => setCurrentPage(p => p + 1)}
                    >
                        Sau
                    </button>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {deleteConfirm && (
                <div className={styles.modalOverlay} onClick={() => setDeleteConfirm(null)}>
                    <div className={styles.modal} onClick={e => e.stopPropagation()}>
                        <button className={styles.modalClose} onClick={() => setDeleteConfirm(null)}>
                            <FiX />
                        </button>
                        <h3>Xác nhận xóa</h3>
                        <p>Bạn có chắc chắn muốn xóa yêu cầu ký gửi này không?</p>
                        <div className={styles.modalActions}>
                            <button
                                className="btn btn-secondary"
                                onClick={() => setDeleteConfirm(null)}
                            >
                                Hủy
                            </button>
                            <button
                                className="btn btn-danger"
                                onClick={() => handleDelete(deleteConfirm)}
                                disabled={deleting}
                            >
                                {deleting ? 'Đang xóa...' : 'Xóa'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
