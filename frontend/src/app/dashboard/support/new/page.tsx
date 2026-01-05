'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { FiArrowLeft } from 'react-icons/fi';
import { supportApi } from '@/lib/api';
import styles from './new.module.css';

const categories = [
    { value: 'general', label: 'Câu hỏi chung' },
    { value: 'payment', label: 'Thanh toán / Nạp tiền' },
    { value: 'consignment', label: 'Ký gửi sản phẩm' },
    { value: 'account', label: 'Tài khoản' },
    { value: 'other', label: 'Khác' },
];

const priorities = [
    { value: 'low', label: 'Thấp', description: 'Không gấp, có thể chờ' },
    { value: 'medium', label: 'Trung bình', description: 'Cần hỗ trợ trong 24h' },
    { value: 'high', label: 'Cao', description: 'Cần hỗ trợ gấp' },
];

export default function NewSupportTicketPage() {
    const router = useRouter();
    const [isLoading, setIsLoading] = useState(false);
    const [formData, setFormData] = useState({
        subject: '',
        category: 'general',
        priority: 'medium',
        message: '',
    });
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [apiError, setApiError] = useState<string | null>(null);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
        setErrors(prev => ({ ...prev, [e.target.name]: '' }));
        setApiError(null);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setApiError(null);

        // Validate
        const newErrors: Record<string, string> = {};
        if (!formData.subject) newErrors.subject = 'Tiêu đề là bắt buộc';
        if (!formData.message) newErrors.message = 'Nội dung là bắt buộc';
        if (formData.message && formData.message.length < 10) newErrors.message = 'Nội dung phải có ít nhất 10 ký tự';

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            setIsLoading(false);
            return;
        }

        try {
            const response = await supportApi.create({
                subject: formData.subject,
                category: formData.category,
                priority: formData.priority,
                message: formData.message,
            });

            if (response.data.success) {
                router.push('/dashboard/support');
            } else {
                setApiError(response.data.message || 'Có lỗi xảy ra');
            }
        } catch (error: any) {
            console.error('Create error:', error);
            if (error.response?.data?.message) {
                setApiError(error.response.data.message);
            } else if (error.response?.data?.errors) {
                const backendErrors = error.response.data.errors;
                const newErrors: Record<string, string> = {};
                Object.keys(backendErrors).forEach(key => {
                    newErrors[key] = backendErrors[key][0];
                });
                setErrors(newErrors);
            } else {
                setApiError('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div>
            <Link href="/dashboard/support" className={styles.backLink}>
                <FiArrowLeft /> Quay lại
            </Link>

            <h1 className={styles.pageTitle}>Tạo yêu cầu hỗ trợ</h1>
            <p className={styles.pageSubtitle}>Mô tả vấn đề của bạn để chúng tôi có thể hỗ trợ tốt nhất</p>

            {apiError && (
                <div className={styles.errorAlert}>{apiError}</div>
            )}

            <form onSubmit={handleSubmit} className={styles.form}>
                <div className="card">
                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="subject">Tiêu đề *</label>
                        <input
                            id="subject"
                            type="text"
                            name="subject"
                            className={`input ${errors.subject ? 'input-error' : ''}`}
                            placeholder="VD: Không thể nạp tiền qua VNPay"
                            value={formData.subject}
                            onChange={handleChange}
                        />
                        {errors.subject && <p className="error-text">{errors.subject}</p>}
                    </div>

                    <div className={styles.formRow}>
                        <div className={styles.formGroup}>
                            <label className="label" htmlFor="category">Danh mục</label>
                            <select
                                id="category"
                                name="category"
                                className="input"
                                value={formData.category}
                                onChange={handleChange}
                            >
                                {categories.map(cat => (
                                    <option key={cat.value} value={cat.value}>{cat.label}</option>
                                ))}
                            </select>
                        </div>

                        <div className={styles.formGroup}>
                            <label className="label" htmlFor="priority">Mức độ ưu tiên</label>
                            <select
                                id="priority"
                                name="priority"
                                className="input"
                                value={formData.priority}
                                onChange={handleChange}
                            >
                                {priorities.map(p => (
                                    <option key={p.value} value={p.value}>{p.label} - {p.description}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="message">Nội dung *</label>
                        <textarea
                            id="message"
                            name="message"
                            className={`input ${errors.message ? 'input-error' : ''}`}
                            rows={6}
                            placeholder="Mô tả chi tiết vấn đề bạn đang gặp phải..."
                            value={formData.message}
                            onChange={handleChange}
                        />
                        {errors.message && <p className="error-text">{errors.message}</p>}
                        <p className={styles.hint}>
                            Hãy cung cấp đủ thông tin để chúng tôi có thể hỗ trợ nhanh chóng:
                            thời gian xảy ra, mã giao dịch (nếu có), ảnh chụp màn hình...
                        </p>
                    </div>
                </div>

                <div className={styles.formActions}>
                    <Link href="/dashboard/support" className="btn btn-secondary">
                        Hủy
                    </Link>
                    <button type="submit" className="btn btn-primary" disabled={isLoading}>
                        {isLoading ? <span className="spinner" /> : 'Gửi yêu cầu'}
                    </button>
                </div>
            </form>
        </div>
    );
}
