'use client';

import { useState, useEffect } from 'react';
import { FiCheck, FiClock, FiPackage, FiShoppingCart, FiZap } from 'react-icons/fi';
import styles from './packages.module.css';

interface PostingPackage {
    id: number;
    name: string;
    slug: string;
    description: string;
    duration_months: number;
    price: number;
    original_price: number | null;
    formatted_price: string;
    formatted_original_price: string | null;
    discount_percentage: number;
    post_limit: number;
    featured_posts: number;
    priority_support: boolean;
    features: string[];
    is_popular: boolean;
}

interface UserPackage {
    id: number;
    package_name: string;
    duration_months: number;
    amount_paid: number;
    started_at: string;
    expires_at: string;
    remaining_days: number;
    posts_used: number;
    remaining_posts: number | string;
    status: 'active' | 'expired' | 'cancelled';
    payment_status: string;
    is_active: boolean;
    payment_method: string;
}

interface WalletInfo {
    balance: number;
}

export default function PackagesPage() {
    const [packages, setPackages] = useState<PostingPackage[]>([]);
    const [myPackages, setMyPackages] = useState<UserPackage[]>([]);
    const [activePackage, setActivePackage] = useState<UserPackage | null>(null);
    const [wallet, setWallet] = useState<WalletInfo | null>(null);
    const [loading, setLoading] = useState(true);
    const [selectedPackage, setSelectedPackage] = useState<PostingPackage | null>(null);
    const [purchasing, setPurchasing] = useState(false);
    const [purchaseError, setPurchaseError] = useState<string | null>(null);

    const API_URL = process.env.NEXT_PUBLIC_API_URL || 'https://api.khodat.com/api';

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        try {
            setLoading(true);
            const token = localStorage.getItem('auth_token');
            const headers: HeadersInit = {
                'Content-Type': 'application/json',
            };
            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            // Load packages (public)
            const packagesRes = await fetch(`${API_URL}/posting-packages`);
            const packagesData = await packagesRes.json();
            if (packagesData.success) {
                setPackages(packagesData.data);
            }

            // Load user's packages and wallet (protected)
            if (token) {
                const [myPackagesRes, walletRes] = await Promise.all([
                    fetch(`${API_URL}/my-packages`, { headers }),
                    fetch(`${API_URL}/user/profile`, { headers }),
                ]);

                const myPackagesData = await myPackagesRes.json();
                if (myPackagesData.success) {
                    setMyPackages(myPackagesData.data.packages || []);
                    setActivePackage(myPackagesData.data.active_package || null);
                }

                const walletData = await walletRes.json();
                if (walletData.data?.wallet) {
                    setWallet(walletData.data.wallet);
                }
            }
        } catch (error) {
            console.error('Error loading data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handlePurchase = async () => {
        if (!selectedPackage) return;

        try {
            setPurchasing(true);
            setPurchaseError(null);

            const token = localStorage.getItem('auth_token');
            const response = await fetch(`${API_URL}/posting-packages/purchase`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({
                    package_id: selectedPackage.id,
                }),
            });

            const data = await response.json();

            if (data.success) {
                alert('Mua gói thành công!');
                setSelectedPackage(null);
                loadData(); // Reload data
            } else {
                setPurchaseError(data.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            setPurchaseError('Có lỗi xảy ra khi mua gói');
        } finally {
            setPurchasing(false);
        }
    };

    const formatPrice = (amount: number): string => {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
    };

    const canAfford = (price: number): boolean => {
        return wallet ? wallet.balance >= price : false;
    };

    if (loading) {
        return (
            <div className={styles.packagesPage}>
                <div className={styles.loading}>
                    <div className={styles.spinner}></div>
                </div>
            </div>
        );
    }

    return (
        <div className={styles.packagesPage}>
            {/* Header */}
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>Gói Đăng Bài</h1>
                <p className={styles.pageSubtitle}>
                    Chọn gói phù hợp để đăng bài ký gửi và tiếp cận khách hàng tiềm năng
                </p>
            </div>

            {/* Current Active Package */}
            {activePackage && (
                <div className={styles.currentPackage}>
                    <div className={styles.currentPackageHeader}>
                        <h3 className={styles.currentPackageTitle}>
                            <FiZap /> Gói đang sử dụng
                        </h3>
                        <span className={styles.currentPackageBadge}>Đang hoạt động</span>
                    </div>
                    <div className={styles.currentPackageInfo}>
                        <div className={styles.packageInfoItem}>
                            <div className={styles.packageInfoLabel}>Tên gói</div>
                            <div className={styles.packageInfoValue}>{activePackage.package_name}</div>
                        </div>
                        <div className={styles.packageInfoItem}>
                            <div className={styles.packageInfoLabel}>Ngày hết hạn</div>
                            <div className={styles.packageInfoValue}>{activePackage.expires_at}</div>
                        </div>
                        <div className={styles.packageInfoItem}>
                            <div className={styles.packageInfoLabel}>Còn lại</div>
                            <div className={styles.packageInfoValue}>{activePackage.remaining_days} ngày</div>
                        </div>
                        <div className={styles.packageInfoItem}>
                            <div className={styles.packageInfoLabel}>Số bài còn lại</div>
                            <div className={styles.packageInfoValue}>{activePackage.remaining_posts}</div>
                        </div>
                    </div>
                </div>
            )}

            {/* Packages Grid */}
            <div className={styles.packagesGrid}>
                {packages.map((pkg) => (
                    <div
                        key={pkg.id}
                        className={`${styles.packageCard} ${pkg.is_popular ? styles.packageCardPopular : ''}`}
                    >
                        {pkg.is_popular && (
                            <span className={styles.popularBadge}>🔥 Phổ biến nhất</span>
                        )}
                        {pkg.discount_percentage > 0 && (
                            <span className={styles.discountBadge}>-{pkg.discount_percentage}%</span>
                        )}

                        <div className={styles.packageDuration}>
                            {pkg.duration_months}
                            <span className={styles.packageDurationSuffix}> tháng</span>
                        </div>

                        <h3 className={styles.packageName}>{pkg.name}</h3>
                        <p className={styles.packageDesc}>{pkg.description}</p>

                        <div className={styles.priceContainer}>
                            {pkg.formatted_original_price && (
                                <div className={styles.originalPrice}>
                                    {pkg.formatted_original_price}
                                </div>
                            )}
                            <div className={styles.currentPrice}>
                                {pkg.formatted_price}
                            </div>
                            <div className={styles.priceNote}>
                                {formatPrice(Math.round(pkg.price / pkg.duration_months))}/tháng
                            </div>
                        </div>

                        <ul className={styles.featuresList}>
                            {pkg.features?.map((feature, index) => (
                                <li key={index} className={styles.featureItem}>
                                    <FiCheck className={styles.featureIcon} size={18} />
                                    <span>{feature}</span>
                                </li>
                            ))}
                        </ul>

                        <button
                            className={`${styles.purchaseBtn} ${pkg.is_popular ? styles.purchaseBtnPrimary : styles.purchaseBtnSecondary}`}
                            onClick={() => setSelectedPackage(pkg)}
                        >
                            <FiShoppingCart style={{ marginRight: 8 }} />
                            Mua ngay
                        </button>
                    </div>
                ))}
            </div>

            {/* My Packages History */}
            {myPackages.length > 0 && (
                <div className={styles.myPackagesSection}>
                    <h2 className={styles.sectionTitle}>
                        <FiClock /> Lịch sử mua gói
                    </h2>
                    <div className={styles.myPackagesTable}>
                        <div className={styles.tableWrapper}>
                            <table className={styles.table}>
                                <thead>
                                    <tr>
                                        <th>Tên gói</th>
                                        <th>Thời hạn</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Ngày hết hạn</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {myPackages.map((pkg) => (
                                        <tr key={pkg.id}>
                                            <td>{pkg.package_name}</td>
                                            <td>{pkg.duration_months} tháng</td>
                                            <td>{pkg.started_at}</td>
                                            <td>{pkg.expires_at}</td>
                                            <td>{formatPrice(pkg.amount_paid)}</td>
                                            <td>
                                                <span
                                                    className={`${styles.statusBadge} ${pkg.is_active
                                                        ? styles.statusActive
                                                        : pkg.status === 'expired'
                                                            ? styles.statusExpired
                                                            : styles.statusPending
                                                        }`}
                                                >
                                                    {pkg.is_active
                                                        ? 'Đang hoạt động'
                                                        : pkg.status === 'expired'
                                                            ? 'Đã hết hạn'
                                                            : 'Đang chờ'}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}

            {/* Purchase Modal */}
            {selectedPackage && (
                <div className={styles.modalOverlay} onClick={() => setSelectedPackage(null)}>
                    <div className={styles.modal} onClick={(e) => e.stopPropagation()}>
                        <div className={styles.modalHeader}>
                            <h2 className={styles.modalTitle}>Xác nhận mua gói</h2>
                            <p className={styles.modalSubtitle}>Kiểm tra thông tin trước khi thanh toán</p>
                        </div>

                        <div className={styles.modalBody}>
                            <div className={styles.summaryCard}>
                                <div className={styles.summaryRow}>
                                    <span>Gói</span>
                                    <span>{selectedPackage.name}</span>
                                </div>
                                <div className={styles.summaryRow}>
                                    <span>Thời hạn</span>
                                    <span>{selectedPackage.duration_months} tháng</span>
                                </div>
                                <div className={styles.summaryRow}>
                                    <span>Số bài đăng</span>
                                    <span>
                                        {selectedPackage.post_limit === -1
                                            ? 'Không giới hạn'
                                            : selectedPackage.post_limit + ' bài'}
                                    </span>
                                </div>
                                <div className={styles.summaryRow}>
                                    <span>Thành tiền</span>
                                    <span>{selectedPackage.formatted_price}</span>
                                </div>
                            </div>

                            <div className={styles.walletBalance}>
                                <div className={styles.walletLabel}>Số dư ví của bạn</div>
                                <div
                                    className={`${styles.walletAmount} ${!canAfford(selectedPackage.price) ? styles.walletInsufficient : ''
                                        }`}
                                >
                                    {wallet ? formatPrice(wallet.balance) : '0 đ'}
                                </div>
                                {!canAfford(selectedPackage.price) && (
                                    <p style={{ color: '#ef4444', marginTop: 8, fontSize: '0.9rem' }}>
                                        Số dư không đủ. Vui lòng nạp thêm tiền.
                                    </p>
                                )}
                            </div>

                            {purchaseError && (
                                <p style={{ color: '#ef4444', marginTop: 16, textAlign: 'center' }}>
                                    {purchaseError}
                                </p>
                            )}
                        </div>

                        <div className={styles.modalActions}>
                            <button
                                className={`${styles.modalBtn} ${styles.modalBtnCancel}`}
                                onClick={() => setSelectedPackage(null)}
                                disabled={purchasing}
                            >
                                Hủy
                            </button>
                            <button
                                className={`${styles.modalBtn} ${styles.modalBtnConfirm}`}
                                onClick={handlePurchase}
                                disabled={!canAfford(selectedPackage.price) || purchasing}
                            >
                                {purchasing ? 'Đang xử lý...' : 'Xác nhận mua'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
