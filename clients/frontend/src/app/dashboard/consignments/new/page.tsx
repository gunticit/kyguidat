'use client';

import { useState, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { FiArrowLeft, FiUpload, FiX, FiFile, FiRotateCw } from 'react-icons/fi';
import Link from 'next/link';
import { consignmentApi, uploadApi } from '@/lib/api';
import { formatCurrencyInput } from '@/lib/formatCurrency';
import { priceToWords } from '@/lib/priceToWords';
import styles from './new.module.css';

interface ImageItem {
    file: File;
    preview: string; // blob URL for preview only
}

export default function NewConsignmentPage() {
    const router = useRouter();
    const [isLoading, setIsLoading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState('');
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        address: '',
        google_map_link: '',
        price: '',
        min_price: '',
        seller_phone: '',
        note_to_admin: '',
    });
    const [imageItems, setImageItems] = useState<ImageItem[]>([]);
    const [descriptionFiles, setDescriptionFiles] = useState<string[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [apiError, setApiError] = useState<string | null>(null);
    const [isQuotaExceeded, setIsQuotaExceeded] = useState(false);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
        setErrors(prev => ({ ...prev, [e.target.name]: '' }));
        setApiError(null);
    };

    const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (files) {
            const newItems: ImageItem[] = Array.from(files).map(file => ({
                file,
                preview: URL.createObjectURL(file),
            }));
            setImageItems(prev => [...prev, ...newItems].slice(0, 20));
        }
    };

    const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (files) {
            const newFiles = Array.from(files).map(file => file.name);
            setDescriptionFiles(prev => [...prev, ...newFiles].slice(0, 5));
        }
    };

    const removeImage = (index: number) => {
        setImageItems(prev => {
            const removed = prev[index];
            if (removed) URL.revokeObjectURL(removed.preview);
            return prev.filter((_, i) => i !== index);
        });
    };

    const rotateImage = async (index: number) => {
        const item = imageItems[index];
        if (!item) return;

        const img = new Image();
        img.src = item.preview;
        await new Promise<void>((resolve) => { img.onload = () => resolve(); });

        const canvas = document.createElement('canvas');
        // Swap width/height for 90° rotation
        canvas.width = img.height;
        canvas.height = img.width;
        const ctx = canvas.getContext('2d')!;
        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.rotate(Math.PI / 2); // 90° clockwise
        ctx.drawImage(img, -img.width / 2, -img.height / 2);

        const blob = await new Promise<Blob>((resolve) => {
            canvas.toBlob((b) => resolve(b!), 'image/jpeg', 0.92);
        });

        const rotatedFile = new File([blob], item.file.name, { type: 'image/jpeg' });
        URL.revokeObjectURL(item.preview);
        const newPreview = URL.createObjectURL(rotatedFile);

        setImageItems(prev =>
            prev.map((it, i) => i === index ? { file: rotatedFile, preview: newPreview } : it)
        );
    };

    const removeFile = (index: number) => {
        setDescriptionFiles(prev => prev.filter((_, i) => i !== index));
    };

    const handlePriceChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        const rawValue = value.replace(/\D/g, '');
        setFormData(prev => ({ ...prev, [name]: rawValue }));
        setErrors(prev => ({ ...prev, [name]: '' }));
        setApiError(null);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setApiError(null);
        setIsQuotaExceeded(false);

        // Validate
        const newErrors: Record<string, string> = {};
        if (!formData.title.trim()) newErrors.title = 'Tiêu đề là bắt buộc';
        if (!formData.address.trim()) newErrors.address = 'Địa chỉ là bắt buộc';
        if (!formData.price) newErrors.price = 'Giá mong muốn là bắt buộc';
        if (formData.price && parseInt(formData.price) < 1000000) newErrors.price = 'Giá tối thiểu là 1,000,000đ';
        if (!formData.seller_phone.trim()) newErrors.seller_phone = 'Số điện thoại là bắt buộc';
        if (formData.seller_phone && !/^[0-9]{10,11}$/.test(formData.seller_phone)) {
            newErrors.seller_phone = 'Số điện thoại phải có 10-11 chữ số';
        }
        if (formData.min_price && parseInt(formData.min_price) > parseInt(formData.price)) {
            newErrors.min_price = 'Giá thấp nhất phải nhỏ hơn hoặc bằng giá mong muốn';
        }
        if (formData.google_map_link && !(/google\.com\/maps|maps\.app\.goo\.gl|goo\.gl\/maps/.test(formData.google_map_link))) {
            newErrors.google_map_link = 'Link Google Map không hợp lệ';
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            setIsLoading(false);
            return;
        }

        try {
            // Step 1: Upload images to server first
            let uploadedImageUrls: string[] = [];
            if (imageItems.length > 0) {
                setUploadProgress('Đang tải ảnh lên...');
                const files = imageItems.map(item => item.file);
                const uploadResponse = await uploadApi.uploadMultiple(files, 'consignments');
                if (uploadResponse.data.success) {
                    uploadedImageUrls = uploadResponse.data.data.map((item: any) => item.url);
                } else {
                    throw new Error(uploadResponse.data.message || 'Upload ảnh thất bại');
                }
            }

            // Step 2: Create consignment with real image URLs
            setUploadProgress('Đang tạo ký gửi...');
            const response = await consignmentApi.create({
                title: formData.title,
                description: formData.description || undefined,
                address: formData.address,
                google_map_link: formData.google_map_link || undefined,
                price: parseInt(formData.price),
                min_price: formData.min_price ? parseInt(formData.min_price) : undefined,
                seller_phone: formData.seller_phone,
                images: uploadedImageUrls.length > 0 ? uploadedImageUrls : undefined,
                description_files: descriptionFiles.length > 0 ? descriptionFiles : undefined,
                note_to_admin: formData.note_to_admin || undefined,
            });

            if (response.data.success) {
                // Clean up blob URLs
                imageItems.forEach(item => URL.revokeObjectURL(item.preview));
                router.push('/dashboard/consignments');
            } else {
                setApiError(response.data.message || 'Có lỗi xảy ra khi tạo ký gửi');
            }
        } catch (error: any) {
            console.error('Create error:', error);
            if (error.response?.status === 403) {
                setApiError(error.response.data.message || 'Bạn đã hết lượt đăng bài. Vui lòng mua gói để tiếp tục đăng.');
                setIsQuotaExceeded(true);
            } else if (error.response?.data?.message) {
                setApiError(error.response.data.message);
            } else if (error.response?.data?.errors) {
                const backendErrors = error.response.data.errors;
                const newErrors: Record<string, string> = {};
                Object.keys(backendErrors).forEach(key => {
                    newErrors[key] = backendErrors[key][0];
                });
                setErrors(newErrors);
            } else {
                setApiError(error.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
            }
        } finally {
            setIsLoading(false);
            setUploadProgress('');
        }
    };

    return (
        <div>
            <Link href="/dashboard/consignments" className={styles.backLink}>
                <FiArrowLeft /> Quay lại
            </Link>

            <h1 className={styles.pageTitle}>Tạo yêu cầu ký gửi</h1>
            <p className={styles.pageSubtitle}>Điền thông tin đất bạn muốn bán</p>

            {apiError && (
                <div className={styles.errorAlert}>
                    <p>{apiError}</p>
                    {isQuotaExceeded && (
                        <Link href="/dashboard/packages" className="btn btn-primary" style={{ marginTop: '12px', display: 'inline-flex' }}>
                            🛒 Mua gói đăng bài
                        </Link>
                    )}
                </div>
            )}

            <form onSubmit={handleSubmit} className={styles.form}>
                {/* Thông tin cơ bản */}
                <div className="card">
                    <h3 className={styles.sectionTitle}>Thông tin cơ bản</h3>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="title">Tiêu đề *</label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            className={`input ${errors.title ? 'input-error' : ''}`}
                            placeholder="VD: Bán đất mặt tiền đường Nguyễn Văn Linh, Quận 7"
                            value={formData.title}
                            onChange={handleChange}
                        />
                        {errors.title && <p className="error-text">{errors.title}</p>}
                    </div>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="description">Nội dung rao bán</label>
                        <textarea
                            id="description"
                            name="description"
                            className="input"
                            rows={5}
                            placeholder="Mô tả chi tiết về đất: diện tích, hướng, pháp lý, quy hoạch, tiện ích xung quanh..."
                            value={formData.description}
                            onChange={handleChange}
                        />
                    </div>
                </div>

                {/* Vị trí */}
                <div className="card">
                    <h3 className={styles.sectionTitle}>Vị trí</h3>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="address">Địa chỉ *</label>
                        <input
                            id="address"
                            type="text"
                            name="address"
                            className={`input ${errors.address ? 'input-error' : ''}`}
                            placeholder="VD: 123 Đường ABC, Phường XYZ, Quận 7, TP.HCM"
                            value={formData.address}
                            onChange={handleChange}
                        />
                        {errors.address && <p className="error-text">{errors.address}</p>}
                    </div>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="google_map_link">Link Google Map</label>
                        <input
                            id="google_map_link"
                            type="text"
                            name="google_map_link"
                            className={`input ${errors.google_map_link ? 'input-error' : ''}`}
                            placeholder="VD: https://maps.app.goo.gl/abc123 hoặc link Google Maps"
                            value={formData.google_map_link}
                            onChange={handleChange}
                        />
                        {errors.google_map_link && <p className="error-text">{errors.google_map_link}</p>}
                        <p className={styles.hint}>Dán link từ Google Maps để người mua dễ dàng xem vị trí</p>
                    </div>
                </div>

                {/* Giá cả */}
                <div className="card">
                    <h3 className={styles.sectionTitle}>Giá cả</h3>

                    <div className={styles.formRow}>
                        <div className={styles.formGroup}>
                            <label className="label" htmlFor="price">Giá mong muốn (VNĐ) *</label>
                            <input
                                id="price"
                                type="text"
                                name="price"
                                className={`input ${errors.price ? 'input-error' : ''}`}
                                placeholder="VD: 5,000,000,000"
                                value={formatCurrencyInput(formData.price)}
                                onChange={handlePriceChange}
                            />
                            {errors.price && <p className="error-text">{errors.price}</p>}
                            {formData.price && (
                                <p style={{ fontSize: '12px', marginTop: '4px', color: 'var(--success)', fontStyle: 'italic' }}>
                                    {priceToWords(formData.price)}
                                </p>
                            )}
                        </div>

                        <div className={styles.formGroup}>
                            <label className="label" htmlFor="min_price">Giá thấp nhất có thể bán (VNĐ)</label>
                            <input
                                id="min_price"
                                type="text"
                                name="min_price"
                                className={`input ${errors.min_price ? 'input-error' : ''}`}
                                placeholder="Để trống nếu không có"
                                value={formatCurrencyInput(formData.min_price)}
                                onChange={handlePriceChange}
                            />
                            {errors.min_price && <p className="error-text">{errors.min_price}</p>}
                            <p className={styles.hint}>Thông tin này chỉ chúng tôi biết, giúp đàm phán tốt hơn</p>
                        </div>
                    </div>
                </div>

                {/* Liên hệ */}
                <div className="card">
                    <h3 className={styles.sectionTitle}>Thông tin liên hệ</h3>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="seller_phone">Số điện thoại người bán *</label>
                        <input
                            id="seller_phone"
                            type="tel"
                            name="seller_phone"
                            className={`input ${errors.seller_phone ? 'input-error' : ''}`}
                            placeholder="VD: 0901234567"
                            value={formData.seller_phone}
                            onChange={handleChange}
                            maxLength={11}
                        />
                        {errors.seller_phone && <p className="error-text">{errors.seller_phone}</p>}
                    </div>
                </div>

                {/* Hình ảnh + File mô tả - 2 columns */}
                <div className={styles.formRow}>
                    <div className="card">
                        <h3 className={styles.sectionTitle}>Hình ảnh đất bán</h3>
                        <p className={styles.hint}>Tối đa 20 hình ảnh, định dạng JPG/PNG. Ảnh rõ nét giúp thu hút người mua.</p>

                        <div className={styles.imageUpload}>
                            <label className={styles.uploadBtn}>
                                <FiUpload size={24} />
                                <span>Tải ảnh lên</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    onChange={handleImageUpload}
                                    style={{ display: 'none' }}
                                />
                            </label>

                            {imageItems.map((item, index) => (
                                <div key={index} className={styles.imagePreview}>
                                    <img src={item.preview} alt={`Preview ${index + 1}`} />
                                    <button
                                        type="button"
                                        className={styles.removeImageBtn}
                                        onClick={() => removeImage(index)}
                                    >
                                        <FiX />
                                    </button>
                                    <button
                                        type="button"
                                        className={styles.rotateImageBtn}
                                        onClick={() => rotateImage(index)}
                                        title="Xoay 90°"
                                    >
                                        <FiRotateCw />
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="card">
                        <h3 className={styles.sectionTitle}>File mô tả (nếu có)</h3>
                        <p className={styles.hint}>Tối đa 5 file, định dạng PDF/DOC/DOCX. VD: giấy tờ đất, bản vẽ, quy hoạch...</p>

                        <div className={styles.fileUpload}>
                            <label className={styles.uploadBtn}>
                                <FiFile size={24} />
                                <span>Tải file lên</span>
                                <input
                                    type="file"
                                    accept=".pdf,.doc,.docx"
                                    multiple
                                    onChange={handleFileUpload}
                                    style={{ display: 'none' }}
                                />
                            </label>
                        </div>

                        {descriptionFiles.length > 0 && (
                            <div className={styles.fileList}>
                                {descriptionFiles.map((file, index) => (
                                    <div key={index} className={styles.fileItem}>
                                        <FiFile />
                                        <span>{file}</span>
                                        <button
                                            type="button"
                                            className={styles.removeFileBtn}
                                            onClick={() => removeFile(index)}
                                        >
                                            <FiX />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Chú thích */}
                <div className="card">
                    <h3 className={styles.sectionTitle}>Chú thích đến người đăng (nếu có)</h3>

                    <div className={styles.formGroup}>
                        <textarea
                            id="note_to_admin"
                            name="note_to_admin"
                            className="input"
                            rows={3}
                            placeholder="Ghi chú thêm cho chúng tôi: thời gian liên hệ phù hợp, yêu cầu đặc biệt..."
                            value={formData.note_to_admin}
                            onChange={handleChange}
                        />
                    </div>
                </div>

                <div className={styles.formActions}>
                    <Link href="/dashboard/consignments" className="btn btn-secondary">
                        Hủy
                    </Link>
                    <button type="submit" className="btn btn-primary" disabled={isLoading}>
                        {isLoading ? <><span className="spinner" /> {uploadProgress}</> : 'Tạo yêu cầu'}
                    </button>
                </div>
            </form>
        </div>
    );
}
