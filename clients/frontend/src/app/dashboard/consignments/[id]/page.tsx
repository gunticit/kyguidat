'use client';

import { useState, useEffect, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { FiArrowLeft, FiEdit, FiTrash2, FiMapPin, FiPhone, FiCalendar, FiDollarSign, FiExternalLink, FiFile, FiImage, FiChevronLeft, FiChevronRight, FiX } from 'react-icons/fi';
import { consignmentApi } from '@/lib/api';
import { formatCurrency } from '@/lib/formatCurrency';
import styles from './detail.module.css';

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
    created_at: string;
    updated_at: string;
    images?: string[];
    description_files?: string[];
    note_to_admin?: string;
}

const statusConfig: Record<string, { label: string; class: string }> = {
    pending: { label: 'Chờ duyệt', class: 'badge-pending' },
    approved: { label: 'Đã duyệt', class: 'badge-info' },
    selling: { label: 'Đang bán', class: 'badge-success' },
    sold: { label: 'Đã bán', class: 'badge-success' },
    cancelled: { label: 'Đã hủy', class: 'badge-error' },
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

export default function ConsignmentDetailPage() {
    const params = useParams();
    const router = useRouter();
    const [consignment, setConsignment] = useState<Consignment | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);

    const openLightbox = (index: number) => setLightboxIndex(index);
    const closeLightbox = () => setLightboxIndex(null);

    const goToPrev = useCallback(() => {
        if (lightboxIndex === null || !consignment?.images) return;
        setLightboxIndex(lightboxIndex === 0 ? consignment.images.length - 1 : lightboxIndex - 1);
    }, [lightboxIndex, consignment?.images]);

    const goToNext = useCallback(() => {
        if (lightboxIndex === null || !consignment?.images) return;
        setLightboxIndex(lightboxIndex === consignment.images.length - 1 ? 0 : lightboxIndex + 1);
    }, [lightboxIndex, consignment?.images]);

    // Keyboard navigation
    useEffect(() => {
        if (lightboxIndex === null) return;
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') goToPrev();
            if (e.key === 'ArrowRight') goToNext();
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [lightboxIndex, goToPrev, goToNext]);

    useEffect(() => {
        const fetchConsignment = async () => {
            try {
                setLoading(true);
                const id = parseInt(params.id as string, 10);
                const response = await consignmentApi.getById(id);
                if (response.data.success) {
                    setConsignment(response.data.data);
                } else {
                    setError('Không thể tải thông tin ký gửi');
                }
            } catch (err: any) {
                console.error('Fetch error:', err);
                if (err.response?.status === 404) {
                    setError('Không tìm thấy yêu cầu ký gửi này');
                } else {
                    setError('Có lỗi xảy ra khi tải dữ liệu');
                }
            } finally {
                setLoading(false);
            }
        };

        if (params.id) {
            fetchConsignment();
        }
    }, [params.id]);

    const handleDelete = async () => {
        if (!consignment) return;

        setDeleting(true);
        try {
            await consignmentApi.delete(consignment.id);
            router.push('/dashboard/consignments');
        } catch (err) {
            console.error('Delete error:', err);
            setError('Không thể xóa yêu cầu ký gửi');
        } finally {
            setDeleting(false);
            setShowDeleteModal(false);
        }
    };

    const handleCancel = async () => {
        if (!consignment) return;

        try {
            const response = await consignmentApi.cancel(consignment.id);
            if (response.data.success) {
                setConsignment(prev => prev ? { ...prev, status: 'cancelled' } : null);
            }
        } catch (err) {
            console.error('Cancel error:', err);
            setError('Không thể hủy yêu cầu ký gửi');
        }
    };

    if (loading) {
        return (
            <div className={styles.loadingContainer}>
                <div className="spinner" />
                <p>Đang tải...</p>
            </div>
        );
    }

    if (error || !consignment) {
        return (
            <div className={styles.errorContainer}>
                <div className={styles.errorCard}>
                    <h2>Lỗi</h2>
                    <p>{error || 'Không tìm thấy dữ liệu'}</p>
                    <Link href="/dashboard/consignments" className="btn btn-primary">
                        Quay lại danh sách
                    </Link>
                </div>
            </div>
        );
    }

    const status = statusConfig[consignment.status] || { label: consignment.status, class: 'badge-info' };

    return (
        <div className={styles.container}>
            {/* Header */}
            <div className={styles.header}>
                <Link href="/dashboard/consignments" className={styles.backLink}>
                    <FiArrowLeft /> Quay lại
                </Link>

                <div className={styles.headerActions}>
                    {consignment.status === 'pending' && (
                        <>
                            <Link
                                href={`/dashboard/consignments/${consignment.id}/edit`}
                                className="btn btn-secondary"
                            >
                                <FiEdit /> Chỉnh sửa
                            </Link>
                            <button
                                className="btn btn-danger"
                                onClick={() => setShowDeleteModal(true)}
                            >
                                <FiTrash2 /> Xóa
                            </button>
                        </>
                    )}
                </div>
            </div>

            {/* Main Content */}
            <div className={styles.content}>
                {/* Title Section */}
                <div className={`card ${styles.titleCard}`}>
                    <div className={styles.codeStatus}>
                        <span className={styles.code}>{consignment.code}</span>
                        <span className={`badge ${status.class}`}>{status.label}</span>
                    </div>
                    <h1 className={styles.title}>{consignment.title}</h1>
                    <div className={styles.meta}>
                        <span><FiCalendar /> Tạo ngày: {formatDate(consignment.created_at)}</span>
                    </div>
                </div>

                {/* Main Grid */}
                <div className={styles.grid}>
                    {/* Left Column */}
                    <div className={styles.leftColumn}>
                        {/* Description */}
                        {consignment.description && (
                            <div className="card">
                                <h3 className={styles.sectionTitle}>Mô tả</h3>
                                <p className={styles.description}>{consignment.description}</p>
                            </div>
                        )}

                        {/* Images */}
                        {consignment.images && consignment.images.length > 0 && (
                            <div className="card">
                                <h3 className={styles.sectionTitle}>
                                    <FiImage /> Hình ảnh ({consignment.images.length})
                                </h3>
                                <div className={styles.imageGrid}>
                                    {consignment.images.map((img, index) => (
                                        <div key={index} className={styles.imageItem} onClick={() => openLightbox(index)} style={{ cursor: 'pointer' }}>
                                            <img src={img} alt={`Ảnh ${index + 1}`} />
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Files */}
                        {consignment.description_files && consignment.description_files.length > 0 && (
                            <div className="card">
                                <h3 className={styles.sectionTitle}>
                                    <FiFile /> File đính kèm ({consignment.description_files.length})
                                </h3>
                                <div className={styles.fileList}>
                                    {consignment.description_files.map((file, index) => (
                                        <a key={index} href={file} target="_blank" rel="noopener noreferrer" className={styles.fileItem}>
                                            <FiFile />
                                            <span>File {index + 1}</span>
                                            <FiExternalLink />
                                        </a>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Note to Admin */}
                        {consignment.note_to_admin && (
                            <div className="card">
                                <h3 className={styles.sectionTitle}>Ghi chú riêng</h3>
                                <p className={styles.noteText}>{consignment.note_to_admin}</p>
                            </div>
                        )}
                    </div>

                    {/* Right Column - Info Card */}
                    <div className={styles.rightColumn}>
                        {/* Price Card */}
                        <div className={`card ${styles.priceCard}`}>
                            <h3 className={styles.sectionTitle}>
                                <FiDollarSign /> Giá cả
                            </h3>
                            <div className={styles.priceMain}>
                                <span className={styles.priceLabel}>Giá mong muốn</span>
                                <span className={styles.priceValue}>
                                    {formatCurrency(consignment.price)}
                                </span>
                            </div>
                            {consignment.min_price && (
                                <div className={styles.priceMin}>
                                    <span className={styles.priceLabel}>Giá tối thiểu</span>
                                    <span className={styles.priceMinValue}>
                                        {formatCurrency(consignment.min_price)}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Location Card */}
                        <div className="card">
                            <h3 className={styles.sectionTitle}>
                                <FiMapPin /> Vị trí
                            </h3>
                            <p className={styles.address}>{consignment.address}</p>
                            {consignment.google_map_link && (
                                <a
                                    href={consignment.google_map_link}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className={styles.mapLink}
                                >
                                    <FiExternalLink /> Xem trên Google Maps
                                </a>
                            )}
                        </div>

                        {/* Contact Card */}
                        <div className="card">
                            <h3 className={styles.sectionTitle}>
                                <FiPhone /> Liên hệ người bán
                            </h3>
                            <a href={`tel:${consignment.seller_phone}`} className={styles.phoneLink}>
                                {consignment.seller_phone}
                            </a>
                        </div>

                        {/* Actions */}
                        {consignment.status === 'pending' && (
                            <div className="card">
                                <h3 className={styles.sectionTitle}>Hành động</h3>
                                <button
                                    className={`btn btn-secondary ${styles.fullWidthBtn}`}
                                    onClick={handleCancel}
                                >
                                    Hủy yêu cầu
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Delete Confirmation Modal */}
            {showDeleteModal && (
                <div className={styles.modalOverlay}>
                    <div className={styles.modal}>
                        <h3>Xác nhận xóa</h3>
                        <p>Bạn có chắc chắn muốn xóa yêu cầu ký gửi này? Hành động này không thể hoàn tác.</p>
                        <div className={styles.modalActions}>
                            <button
                                className="btn btn-secondary"
                                onClick={() => setShowDeleteModal(false)}
                                disabled={deleting}
                            >
                                Hủy
                            </button>
                            <button
                                className="btn btn-danger"
                                onClick={handleDelete}
                                disabled={deleting}
                            >
                                {deleting ? <span className="spinner" /> : 'Xóa'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Image Lightbox */}
            {lightboxIndex !== null && consignment.images && (
                <div className={styles.lightboxOverlay} onClick={closeLightbox}>
                    <button className={styles.lightboxClose} onClick={closeLightbox}>
                        <FiX />
                    </button>

                    <button
                        className={`${styles.lightboxNav} ${styles.lightboxPrev}`}
                        onClick={(e) => { e.stopPropagation(); goToPrev(); }}
                    >
                        <FiChevronLeft />
                    </button>

                    <div className={styles.lightboxContent} onClick={(e) => e.stopPropagation()}>
                        <img
                            src={consignment.images[lightboxIndex]}
                            alt={`Ảnh ${lightboxIndex + 1}`}
                            className={styles.lightboxImage}
                        />
                        <div className={styles.lightboxCounter}>
                            {lightboxIndex + 1} / {consignment.images.length}
                        </div>
                    </div>

                    <button
                        className={`${styles.lightboxNav} ${styles.lightboxNext}`}
                        onClick={(e) => { e.stopPropagation(); goToNext(); }}
                    >
                        <FiChevronRight />
                    </button>
                </div>
            )}
        </div>
    );
}
