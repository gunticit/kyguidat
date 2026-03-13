'use client';

import { useState, useEffect, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
    FiArrowLeft, FiEdit, FiTrash2, FiMapPin, FiPhone, FiCalendar,
    FiDollarSign, FiExternalLink, FiFile, FiImage, FiChevronLeft,
    FiChevronRight, FiX, FiUser, FiGrid, FiCompass, FiHome,
    FiLayers, FiMaximize, FiRefreshCw, FiClock, FiAlertTriangle,
    FiCheckCircle, FiXCircle, FiPauseCircle
} from 'react-icons/fi';
import { consignmentApi } from '@/lib/api';
import { formatCurrency } from '@/lib/formatCurrency';
import styles from './detail.module.css';

interface Consignment {
    id: number;
    user_id?: number;
    code: string;
    title: string;
    description?: string;
    address: string;
    google_map_link?: string;
    price: number;
    min_price?: number;
    seller_phone?: string;
    consigner_name?: string;
    consigner_phone?: string;
    consigner_type?: string;
    status: string;
    reject_reason?: string;
    created_at: string;
    updated_at: string;
    published_at?: string;
    deactivated_at?: string;
    auto_deactivated?: boolean;
    images?: string[];
    description_files?: string[];
    note_to_admin?: string;
    province?: string;
    ward?: string;
    category?: string;
    land_directions?: string[];
    land_types?: string[];
    area_dimensions?: string;
    area_range?: string;
    frontage_range?: string;
    frontage_actual?: string;
    has_house?: string;
    residential_area?: number;
    residential_type?: string;
    road?: string;
    road_display?: string;
    floor_area?: number;
    sheet_number?: string;
    parcel_number?: string;
    order_number?: number;
    featured_image?: string;
    seo_url?: string;
    latitude?: string;
    longitude?: string;
}

const statusConfig: Record<string, { label: string; class: string; icon: any; color: string }> = {
    pending: { label: 'Chờ duyệt', class: 'badge-pending', icon: FiClock, color: '#f59e0b' },
    approved: { label: 'Đã duyệt', class: 'badge-info', icon: FiCheckCircle, color: '#6366f1' },
    rejected: { label: 'Từ chối', class: 'badge-error', icon: FiXCircle, color: '#ef4444' },
    selling: { label: 'Đang bán', class: 'badge-success', icon: FiCheckCircle, color: '#10b981' },
    sold: { label: 'Đã bán', class: 'badge-success', icon: FiCheckCircle, color: '#8b5cf6' },
    cancelled: { label: 'Đã hủy', class: 'badge-error', icon: FiXCircle, color: '#ef4444' },
    deactivated: { label: 'Đã tắt', class: 'badge-warning', icon: FiPauseCircle, color: '#f97316' },
};

const directionMap: Record<string, string> = {
    dong: 'Đông', tay: 'Tây', nam: 'Nam', bac: 'Bắc',
    dong_bac: 'Đông Bắc', dong_nam: 'Đông Nam', tay_bac: 'Tây Bắc', tay_nam: 'Tây Nam',
};

const landTypeMap: Record<string, string> = {
    dat_nen: 'Đất nền', dat_nong_nghiep: 'Đất nông nghiệp', dat_cong_nghiep: 'Đất công nghiệp',
    dat_thu_cu: 'Đất thổ cư', dat_rung: 'Đất rừng', khac: 'Khác',
};

const consignerTypeMap: Record<string, string> = {
    chinh_chu: 'Chính chủ', moi_gioi: 'Môi giới', uy_quyen: 'Ủy quyền',
};

const areaRangeMap: Record<string, string> = {
    'duoi_100': '< 100 m²', '100_200': '100 - 200 m²', '200_500': '200 - 500 m²',
    '500_1000': '500 - 1.000 m²', 'tren_1000': '> 1.000 m²',
};

const frontageRangeMap: Record<string, string> = {
    'duoi_5m': '< 5m', '5_10m': '5 - 10m', '10_20m': '10 - 20m', 'tren_20m': '> 20m',
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const getDaysRemaining = (item: Consignment): number | null => {
    if (!['approved', 'selling'].includes(item.status)) return null;
    const refDate = item.published_at || item.created_at;
    if (!refDate) return null;
    const publishDate = new Date(refDate);
    const expireDate = new Date(publishDate.getTime() + 30 * 24 * 60 * 60 * 1000);
    return Math.ceil((expireDate.getTime() - Date.now()) / (1000 * 60 * 60 * 24));
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
    const [priceModal, setPriceModal] = useState(false);
    const [newPrice, setNewPrice] = useState('');
    const [updatingPrice, setUpdatingPrice] = useState(false);
    const [reactivating, setReactivating] = useState(false);

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
                const resData = response.data;
                if (resData.success) {
                    setConsignment(resData.data);
                } else if (resData.id) {
                    setConsignment(resData);
                } else {
                    setError('Không thể tải thông tin ký gửi');
                }
            } catch (err: any) {
                if (err.response?.status === 404) {
                    setError('Không tìm thấy yêu cầu ký gửi này');
                } else {
                    setError('Có lỗi xảy ra khi tải dữ liệu');
                }
            } finally {
                setLoading(false);
            }
        };
        if (params.id) fetchConsignment();
    }, [params.id]);

    const handleDelete = async () => {
        if (!consignment) return;
        setDeleting(true);
        try {
            await consignmentApi.delete(consignment.id);
            router.push('/dashboard/consignments');
        } catch {
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
        } catch {
            setError('Không thể hủy yêu cầu ký gửi');
        }
    };

    const handleUpdatePrice = async () => {
        if (!consignment || !newPrice) return;
        setUpdatingPrice(true);
        try {
            const response = await consignmentApi.updatePrice(consignment.id, parseFloat(newPrice));
            if (response.data.success) {
                setConsignment(prev => prev ? { ...prev, price: parseFloat(newPrice) } : null);
                setPriceModal(false);
                setNewPrice('');
            } else {
                alert(response.data.message || 'Không thể cập nhật giá');
            }
        } catch {
            alert('Có lỗi xảy ra khi cập nhật giá');
        } finally {
            setUpdatingPrice(false);
        }
    };

    const handleReactivate = async () => {
        if (!consignment) return;
        setReactivating(true);
        try {
            const response = await consignmentApi.reactivate(consignment.id);
            if (response.data.success) {
                setConsignment(prev => prev ? { ...prev, status: 'selling', deactivated_at: undefined, auto_deactivated: false } : null);
            } else {
                alert(response.data.message || 'Không thể bật lại');
            }
        } catch {
            alert('Có lỗi xảy ra khi bật lại bài đăng');
        } finally {
            setReactivating(false);
        }
    };

    if (loading) {
        return (
            <div className={styles.loadingContainer}>
                <div className="spinner" />
                <p>Đang tải thông tin...</p>
            </div>
        );
    }

    if (error || !consignment) {
        return (
            <div className={styles.errorContainer}>
                <div className={styles.errorCard}>
                    <FiAlertTriangle size={48} style={{ color: 'var(--error)', marginBottom: 16 }} />
                    <h2>Lỗi</h2>
                    <p>{error || 'Không tìm thấy dữ liệu'}</p>
                    <Link href="/dashboard/consignments" className="btn btn-primary">
                        Quay lại danh sách
                    </Link>
                </div>
            </div>
        );
    }

    const status = statusConfig[consignment.status] || { label: consignment.status, class: 'badge-info', icon: FiClock, color: '#6b7280' };
    const StatusIcon = status.icon;
    const daysRemaining = getDaysRemaining(consignment);

    // Check ownership: only the consignment owner can edit/delete/update price
    let isOwner = false;
    try {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            const currentUser = JSON.parse(storedUser);
            isOwner = currentUser?.id === consignment.user_id;
        }
    } catch { /* ignore */ }

    const canUpdatePrice = isOwner && ['approved', 'selling', 'deactivated'].includes(consignment.status);
    const canDelete = isOwner && ['pending', 'rejected', 'cancelled', 'approved', 'selling', 'deactivated'].includes(consignment.status);
    const canReactivate = isOwner && consignment.status === 'deactivated';
    const canEdit = isOwner && consignment.status === 'pending';

    return (
        <div className={styles.container}>
            {/* Header */}
            <div className={styles.header}>
                <Link href="/dashboard/consignments" className={styles.backLink}>
                    <FiArrowLeft /> Quay lại danh sách
                </Link>
                <div className={styles.headerActions}>
                    {canUpdatePrice && (
                        <button className="btn btn-secondary" onClick={() => { setPriceModal(true); setNewPrice(String(consignment.price)); }}>
                            <FiDollarSign /> Cập nhật giá
                        </button>
                    )}
                    {canReactivate && (
                        <button className="btn btn-primary" onClick={handleReactivate} disabled={reactivating}>
                            <FiRefreshCw /> {reactivating ? 'Đang xử lý...' : 'Bật lại'}
                        </button>
                    )}
                    {canEdit && (
                        <Link href={`/dashboard/consignments/${consignment.id}/edit`} className="btn btn-secondary">
                            <FiEdit /> Chỉnh sửa
                        </Link>
                    )}
                    {canDelete && (
                        <button className="btn btn-danger" onClick={() => setShowDeleteModal(true)}>
                            <FiTrash2 /> Xóa
                        </button>
                    )}
                </div>
            </div>

            {/* Status Banner */}
            <div className={styles.statusBanner} style={{ borderLeftColor: status.color }}>
                <div className={styles.statusBannerLeft}>
                    <StatusIcon size={22} style={{ color: status.color }} />
                    <div>
                        <span className={styles.statusLabel}>Trạng thái</span>
                        <span className={styles.statusValue} style={{ color: status.color }}>{status.label}</span>
                    </div>
                </div>
                {daysRemaining !== null && (
                    <div className={styles.daysRemaining} style={{ color: daysRemaining <= 7 ? '#ef4444' : '#f59e0b' }}>
                        <FiClock /> {daysRemaining > 0 ? `Còn ${daysRemaining} ngày hiển thị` : 'Sắp bị tắt tự động'}
                    </div>
                )}
                {consignment.status === 'deactivated' && consignment.auto_deactivated && (
                    <div className={styles.daysRemaining} style={{ color: '#f97316' }}>
                        <FiPauseCircle /> Tắt tự động sau 30 ngày
                    </div>
                )}
            </div>

            {/* Reject reason */}
            {consignment.status === 'rejected' && consignment.reject_reason && (
                <div className={styles.rejectBanner}>
                    <FiXCircle size={18} />
                    <div>
                        <strong>Lý do từ chối:</strong> {consignment.reject_reason}
                    </div>
                </div>
            )}

            {/* Main Content */}
            <div className={styles.content}>
                {/* Title Section */}
                <div className={`card ${styles.titleCard}`}>
                    <div className={styles.codeStatus}>
                        <span className={styles.code}>{consignment.code}</span>
                        {consignment.order_number && (
                            <span className={styles.orderNum}>#{consignment.order_number}</span>
                        )}
                        {consignment.category && (
                            <span className={styles.categoryBadge}>{consignment.category}</span>
                        )}
                    </div>
                    <h1 className={styles.title}>{consignment.title}</h1>
                    <div className={styles.meta}>
                        <span><FiCalendar /> Tạo: {formatDate(consignment.created_at)}</span>
                        {consignment.published_at && (
                            <span><FiCheckCircle /> Đăng: {formatDate(consignment.published_at)}</span>
                        )}
                        {consignment.province && (
                            <span><FiMapPin /> {consignment.ward ? `${consignment.ward}, ` : ''}{consignment.province}</span>
                        )}
                    </div>
                </div>

                {/* Main Grid */}
                <div className={styles.grid}>
                    {/* Left Column */}
                    <div className={styles.leftColumn}>
                        {/* Images */}
                        {consignment.images && consignment.images.length > 0 && (
                            <div className="card">
                                <h3 className={styles.sectionTitle}>
                                    <FiImage /> Hình ảnh ({consignment.images.length})
                                </h3>
                                <div className={styles.imageGrid}>
                                    {consignment.images.map((img, index) => (
                                        <div key={index} className={styles.imageItem} onClick={() => openLightbox(index)}>
                                            <img src={img} alt={`Ảnh ${index + 1}`} />
                                            <div className={styles.imageOverlay}>
                                                <FiMaximize />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Description */}
                        {consignment.description && (
                            <div className="card">
                                <h3 className={styles.sectionTitle}>Mô tả chi tiết</h3>
                                <div
                                    className={styles.description}
                                    dangerouslySetInnerHTML={{ __html: consignment.description }}
                                />
                            </div>
                        )}

                        {/* Property Details */}
                        <div className="card">
                            <h3 className={styles.sectionTitle}>
                                <FiGrid /> Thông tin bất động sản
                            </h3>
                            <div className={styles.propertyGrid}>
                                {consignment.area_dimensions && (
                                    <div className={styles.propertyItem}>
                                        <FiMaximize className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Kích thước</span>
                                            <span className={styles.propertyValue}>{consignment.area_dimensions}</span>
                                        </div>
                                    </div>
                                )}
                                {consignment.area_range && (
                                    <div className={styles.propertyItem}>
                                        <FiLayers className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Diện tích</span>
                                            <span className={styles.propertyValue}>{areaRangeMap[consignment.area_range] || consignment.area_range}</span>
                                        </div>
                                    </div>
                                )}
                                {consignment.frontage_range && (
                                    <div className={styles.propertyItem}>
                                        <FiMaximize className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Mặt tiền</span>
                                            <span className={styles.propertyValue}>{frontageRangeMap[consignment.frontage_range] || consignment.frontage_range}</span>
                                        </div>
                                    </div>
                                )}
                                {consignment.land_directions && consignment.land_directions.length > 0 && (
                                    <div className={styles.propertyItem}>
                                        <FiCompass className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Hướng</span>
                                            <span className={styles.propertyValue}>{consignment.land_directions.map(d => directionMap[d] || d).join(', ')}</span>
                                        </div>
                                    </div>
                                )}
                                {consignment.land_types && consignment.land_types.length > 0 && (
                                    <div className={styles.propertyItem}>
                                        <FiGrid className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Loại đất</span>
                                            <span className={styles.propertyValue}>{consignment.land_types.map(t => landTypeMap[t] || t).join(', ')}</span>
                                        </div>
                                    </div>
                                )}
                                {consignment.has_house && (
                                    <div className={styles.propertyItem}>
                                        <FiHome className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Có nhà</span>
                                            <span className={styles.propertyValue}>{consignment.has_house === 'co' ? 'Có' : 'Không'}</span>
                                        </div>
                                    </div>
                                )}
                                {consignment.residential_area && consignment.residential_area > 0 && (
                                    <div className={styles.propertyItem}>
                                        <FiHome className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Diện tích thổ cư</span>
                                            <span className={styles.propertyValue}>{consignment.residential_area} m²</span>
                                        </div>
                                    </div>
                                )}
                                {consignment.road && (
                                    <div className={styles.propertyItem}>
                                        <FiMapPin className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Đường</span>
                                            <span className={styles.propertyValue}>{consignment.road}</span>
                                        </div>
                                    </div>
                                )}
                                {(consignment.sheet_number || consignment.parcel_number) && (
                                    <div className={styles.propertyItem}>
                                        <FiFile className={styles.propertyIcon} />
                                        <div>
                                            <span className={styles.propertyLabel}>Tờ / Thửa</span>
                                            <span className={styles.propertyValue}>{consignment.sheet_number || '-'} / {consignment.parcel_number || '-'}</span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

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

                    {/* Right Column */}
                    <div className={styles.rightColumn}>
                        {/* Price Card */}
                        <div className={`card ${styles.priceCard}`}>
                            <h3 className={styles.sectionTitle}>
                                <FiDollarSign /> Giá cả
                            </h3>
                            <div className={styles.priceMain}>
                                <span className={styles.priceLabel}>Giá bán</span>
                                <span className={styles.priceValue}>
                                    {formatCurrency(consignment.price)}
                                </span>
                            </div>
                            {Number(consignment.min_price) > 0 && (
                                <div className={styles.priceMin}>
                                    <span className={styles.priceLabel}>Giá tối thiểu</span>
                                    <span className={styles.priceMinValue}>
                                        {formatCurrency(consignment.min_price)}
                                    </span>
                                </div>
                            )}
                            {canUpdatePrice && (
                                <button
                                    className={`btn btn-primary ${styles.fullWidthBtn}`}
                                    onClick={() => { setPriceModal(true); setNewPrice(String(consignment.price)); }}
                                    style={{ marginTop: 16 }}
                                >
                                    <FiDollarSign /> Cập nhật giá
                                </button>
                            )}
                        </div>

                        {/* Location Card */}
                        <div className="card">
                            <h3 className={styles.sectionTitle}>
                                <FiMapPin /> Vị trí
                            </h3>
                            <p className={styles.address}>{consignment.address}</p>
                            {consignment.google_map_link && (
                                <a href={consignment.google_map_link} target="_blank" rel="noopener noreferrer" className={styles.mapLink}>
                                    <FiExternalLink /> Xem trên Google Maps
                                </a>
                            )}
                        </div>

                        {/* Consigner Card */}
                        <div className="card">
                            <h3 className={styles.sectionTitle}>
                                <FiUser /> Người ký gửi
                            </h3>
                            <div className={styles.consignerInfo}>
                                {consignment.consigner_name && (
                                    <div className={styles.consignerRow}>
                                        <span className={styles.consignerLabel}>Tên:</span>
                                        <span>{consignment.consigner_name}</span>
                                    </div>
                                )}
                                {consignment.consigner_phone && (
                                    <div className={styles.consignerRow}>
                                        <span className={styles.consignerLabel}>SĐT:</span>
                                        <a href={`tel:${consignment.consigner_phone}`} className={styles.phoneLink}>
                                            {consignment.consigner_phone}
                                        </a>
                                    </div>
                                )}
                                {consignment.consigner_type && (
                                    <div className={styles.consignerRow}>
                                        <span className={styles.consignerLabel}>Loại:</span>
                                        <span>{consignerTypeMap[consignment.consigner_type] || consignment.consigner_type}</span>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Actions Card */}
                        <div className="card">
                            <h3 className={styles.sectionTitle}>Hành động</h3>
                            <div className={styles.actionsList}>
                                {canReactivate && (
                                    <button className={`btn btn-primary ${styles.fullWidthBtn}`} onClick={handleReactivate} disabled={reactivating}>
                                        <FiRefreshCw /> {reactivating ? 'Đang xử lý...' : 'Bật lại bài đăng'}
                                    </button>
                                )}
                                {consignment.status === 'pending' && (
                                    <button className={`btn btn-secondary ${styles.fullWidthBtn}`} onClick={handleCancel}>
                                        Hủy yêu cầu
                                    </button>
                                )}
                                {consignment.seo_url && ['approved', 'selling'].includes(consignment.status) && (
                                    <a
                                        href={`https://khodat.com/bat-dong-san/${consignment.seo_url}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className={`btn btn-secondary ${styles.fullWidthBtn}`}
                                    >
                                        <FiExternalLink /> Xem ngoài sàn
                                    </a>
                                )}
                                {canDelete && (
                                    <button className={`btn btn-danger ${styles.fullWidthBtn}`} onClick={() => setShowDeleteModal(true)}>
                                        <FiTrash2 /> Xóa bài đăng
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Price Update Modal */}
            {priceModal && (
                <div className={styles.modalOverlay} onClick={() => setPriceModal(false)}>
                    <div className={styles.modal} onClick={e => e.stopPropagation()}>
                        <h3><FiDollarSign /> Cập nhật giá</h3>
                        <p>Nhập giá mới cho bài đăng này:</p>
                        <div className={styles.priceInputGroup}>
                            <input
                                type="number"
                                value={newPrice}
                                onChange={e => setNewPrice(e.target.value)}
                                placeholder="Nhập giá mới"
                                className={styles.priceInput}
                                min="0"
                            />
                            <span className={styles.priceUnit}>VNĐ</span>
                        </div>
                        {newPrice && (
                            <p className={styles.pricePreview}>
                                = {formatCurrency(parseFloat(newPrice) || 0)}
                            </p>
                        )}
                        <div className={styles.modalActions}>
                            <button className="btn btn-secondary" onClick={() => setPriceModal(false)} disabled={updatingPrice}>
                                Hủy
                            </button>
                            <button className="btn btn-primary" onClick={handleUpdatePrice} disabled={updatingPrice || !newPrice}>
                                {updatingPrice ? <span className="spinner" /> : 'Cập nhật'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {showDeleteModal && (
                <div className={styles.modalOverlay} onClick={() => setShowDeleteModal(false)}>
                    <div className={styles.modal} onClick={e => e.stopPropagation()}>
                        <h3>Xác nhận xóa</h3>
                        <p>Bạn có chắc chắn muốn xóa bài đăng <strong>{consignment.title}</strong>? Hành động này không thể hoàn tác.</p>
                        <div className={styles.modalActions}>
                            <button className="btn btn-secondary" onClick={() => setShowDeleteModal(false)} disabled={deleting}>
                                Hủy
                            </button>
                            <button className="btn btn-danger" onClick={handleDelete} disabled={deleting}>
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
                    <button className={`${styles.lightboxNav} ${styles.lightboxPrev}`} onClick={(e) => { e.stopPropagation(); goToPrev(); }}>
                        <FiChevronLeft />
                    </button>
                    <div className={styles.lightboxContent} onClick={(e) => e.stopPropagation()}>
                        <img src={consignment.images[lightboxIndex]} alt={`Ảnh ${lightboxIndex + 1}`} className={styles.lightboxImage} />
                        <div className={styles.lightboxCounter}>
                            {lightboxIndex + 1} / {consignment.images.length}
                        </div>
                    </div>
                    <button className={`${styles.lightboxNav} ${styles.lightboxNext}`} onClick={(e) => { e.stopPropagation(); goToNext(); }}>
                        <FiChevronRight />
                    </button>
                </div>
            )}
        </div>
    );
}
