'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import styles from './slug.module.css';

interface ConsignmentData {
    id: number;
    title: string;
    code: string;
    description: string;
    price: number;
    address: string;
    province: string;
    ward: string;
    area_dimensions: string;
    frontage_actual: number;
    road: string;
    type: string;
    land_directions: string[];
    land_types: string[];
    has_house: string;
    featured_image: string;
    images: string[];
    seo_url: string;
    status: string;
    created_at: string;
    user?: { id: number; name: string };
}

function formatPrice(price: number): string {
    if (price >= 1_000_000_000) {
        return (price / 1_000_000_000).toFixed(1).replace(/\.0$/, '') + ' tỷ';
    }
    if (price >= 1_000_000) {
        return (price / 1_000_000).toFixed(0) + ' triệu';
    }
    return price.toLocaleString('vi-VN', { maximumFractionDigits: 0 }) + ' đ';
}

export default function ConsignmentBySlugPage() {
    const params = useParams();
    const slug = params.slug as string;
    const [data, setData] = useState<ConsignmentData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        if (!slug) return;

        const fetchData = async () => {
            try {
                const apiUrl = process.env.NEXT_PUBLIC_API_URL || '/api';
                const res = await fetch(`${apiUrl}/public/consignments/by-slug/${slug}`);
                const json = await res.json();
                if (json.success) {
                    setData(json.data);
                } else {
                    setError(json.message || 'Không tìm thấy bất động sản');
                }
            } catch {
                setError('Lỗi kết nối server');
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [slug]);

    if (loading) {
        return (
            <div className={styles.container}>
                <div className={styles.loading}>
                    <div className={styles.spinner}></div>
                    <p>Đang tải thông tin...</p>
                </div>
            </div>
        );
    }

    if (error || !data) {
        return (
            <div className={styles.container}>
                <div className={styles.errorCard}>
                    <h2>Không tìm thấy</h2>
                    <p>{error || 'Bất động sản không tồn tại hoặc đã bị gỡ'}</p>
                    <Link href="/" className={styles.backBtn}>← Về trang chủ</Link>
                </div>
            </div>
        );
    }

    return (
        <div className={styles.container}>
            <nav className={styles.breadcrumb}>
                <Link href="/">Trang chủ</Link>
                <span>/</span>
                <span>{data.title}</span>
            </nav>

            <div className={styles.grid}>
                {/* Main content */}
                <div className={styles.main}>
                    {/* Image gallery */}
                    {data.featured_image && (
                        <div className={styles.imageSection}>
                            <img src={data.featured_image} alt={data.title} className={styles.featuredImage} />
                        </div>
                    )}

                    {data.images && data.images.length > 0 && (
                        <div className={styles.gallery}>
                            {data.images.map((img, i) => (
                                <img key={i} src={img} alt={`${data.title} - ${i + 1}`} className={styles.galleryImage} />
                            ))}
                        </div>
                    )}

                    <h1 className={styles.title}>{data.title}</h1>
                    <p className={styles.code}>Mã: {data.code}</p>

                    {data.description && (
                        <div className={styles.description} dangerouslySetInnerHTML={{ __html: data.description }} />
                    )}
                </div>

                {/* Sidebar */}
                <aside className={styles.sidebar}>
                    <div className={styles.priceCard}>
                        <div className={styles.price}>{formatPrice(data.price)}</div>
                    </div>

                    <div className={styles.infoCard}>
                        <h3>Thông tin chi tiết</h3>
                        <ul className={styles.infoList}>
                            {data.address && (
                                <li><span>Địa chỉ:</span> <strong>{data.address}</strong></li>
                            )}
                            {data.province && (
                                <li><span>Tỉnh/TP:</span> <strong>{data.province}</strong></li>
                            )}
                            {data.ward && (
                                <li><span>Phường/Xã:</span> <strong>{data.ward}</strong></li>
                            )}
                            {data.area_dimensions && (
                                <li><span>Diện tích:</span> <strong>{data.area_dimensions}</strong></li>
                            )}
                            {data.frontage_actual && (
                                <li><span>Mặt tiền:</span> <strong>{parseFloat(String(data.frontage_actual))} m</strong></li>
                            )}
                            {data.road && (
                                <li><span>Đường:</span> <strong>{data.road}</strong></li>
                            )}
                            {data.type && (
                                <li><span>Loại:</span> <strong>{data.type}</strong></li>
                            )}
                            {data.has_house && (
                                <li><span>Có nhà:</span> <strong>{data.has_house === 'yes' ? 'Có' : 'Không'}</strong></li>
                            )}
                            {data.land_directions && data.land_directions.length > 0 && (
                                <li><span>Hướng:</span> <strong>{data.land_directions.join(', ')}</strong></li>
                            )}
                        </ul>
                    </div>

                    <div className={styles.contactCard}>
                        <h3>Liên hệ</h3>
                        <p>Để biết thêm chi tiết, vui lòng liên hệ</p>
                        <Link href="/dashboard/support/new" className={styles.contactBtn}>Liên hệ tư vấn</Link>
                    </div>
                </aside>
            </div>
        </div>
    );
}
