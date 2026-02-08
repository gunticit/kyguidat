'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { authApi } from '@/lib/api';
import styles from '../login/login.module.css';

export default function RegisterPage() {
    const router = useRouter();
    const [isLoading, setIsLoading] = useState(false);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [generalError, setGeneralError] = useState<string>('');

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
        setErrors(prev => ({ ...prev, [e.target.name]: '' }));
        setGeneralError('');
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setGeneralError('');

        // Validate
        const newErrors: Record<string, string> = {};
        if (!formData.name) newErrors.name = 'Họ tên là bắt buộc';
        if (!formData.email) newErrors.email = 'Email là bắt buộc';
        if (!formData.password) newErrors.password = 'Mật khẩu là bắt buộc';
        if (formData.password.length < 6) newErrors.password = 'Mật khẩu tối thiểu 6 ký tự';
        if (formData.password !== formData.password_confirmation) {
            newErrors.password_confirmation = 'Xác nhận mật khẩu không khớp';
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            setIsLoading(false);
            return;
        }

        try {
            const response = await authApi.register(formData);

            if (response.data.success) {
                // Lưu token vào localStorage
                localStorage.setItem('auth_token', response.data.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.data.user));

                // Redirect đến dashboard
                router.push('/dashboard');
            } else {
                setGeneralError(response.data.message || 'Đăng ký thất bại');
            }
        } catch (error: unknown) {
            console.error('Register error:', error);

            // Xử lý lỗi từ axios
            interface AxiosError {
                response?: {
                    data?: {
                        message?: string;
                        errors?: Record<string, string[]>;
                    };
                    status?: number;
                };
            }

            const axiosError = error as AxiosError;
            if (axiosError.response?.data) {
                const errorData = axiosError.response.data;

                if (errorData.message) {
                    setGeneralError(errorData.message);
                }

                // Xử lý validation errors
                if (errorData.errors) {
                    const fieldErrors: Record<string, string> = {};
                    Object.entries(errorData.errors).forEach(([field, messages]) => {
                        if (Array.isArray(messages) && messages.length > 0) {
                            fieldErrors[field] = messages[0];
                        }
                    });
                    setErrors(fieldErrors);
                }
            } else {
                setGeneralError('Có lỗi xảy ra. Vui lòng thử lại sau.');
            }
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className={styles.container}>
            <div className={styles.formWrapper}>
                <div className={styles.header}>
                    <h1 className={styles.title}>Đăng ký</h1>
                    <p className={styles.subtitle}>Tạo tài khoản mới để bắt đầu</p>
                </div>

                {/* General Error */}
                {generalError && (
                    <div className="error-box" style={{
                        background: 'rgba(239, 68, 68, 0.1)',
                        border: '1px solid rgba(239, 68, 68, 0.3)',
                        borderRadius: '8px',
                        padding: '12px 16px',
                        marginBottom: '16px',
                        color: '#ef4444',
                        fontSize: '0.9rem',
                        textAlign: 'center',
                    }}>
                        {generalError}
                    </div>
                )}

                <form onSubmit={handleSubmit} className={styles.form}>
                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="name">Họ tên</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            className={`input ${errors.name ? 'input-error' : ''}`}
                            placeholder="Nguyễn Văn A"
                            value={formData.name}
                            onChange={handleChange}
                        />
                        {errors.name && <p className="error-text">{errors.name}</p>}
                    </div>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            className={`input ${errors.email ? 'input-error' : ''}`}
                            placeholder="your@email.com"
                            value={formData.email}
                            onChange={handleChange}
                        />
                        {errors.email && <p className="error-text">{errors.email}</p>}
                    </div>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="phone">Số điện thoại (tùy chọn)</label>
                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            className="input"
                            placeholder="0901234567"
                            value={formData.phone}
                            onChange={handleChange}
                        />
                    </div>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="password">Mật khẩu</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            className={`input ${errors.password ? 'input-error' : ''}`}
                            placeholder="••••••••"
                            value={formData.password}
                            onChange={handleChange}
                        />
                        {errors.password && <p className="error-text">{errors.password}</p>}
                    </div>

                    <div className={styles.formGroup}>
                        <label className="label" htmlFor="password_confirmation">Xác nhận mật khẩu</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            className={`input ${errors.password_confirmation ? 'input-error' : ''}`}
                            placeholder="••••••••"
                            value={formData.password_confirmation}
                            onChange={handleChange}
                        />
                        {errors.password_confirmation && <p className="error-text">{errors.password_confirmation}</p>}
                    </div>

                    <button
                        type="submit"
                        className="btn btn-primary"
                        style={{ width: '100%' }}
                        disabled={isLoading}
                    >
                        {isLoading ? <span className="spinner" /> : 'Đăng ký'}
                    </button>
                </form>

                <p className={styles.registerLink}>
                    Đã có tài khoản? <Link href="/login">Đăng nhập</Link>
                </p>
            </div>

            <div className={styles.decorative}>
                <div className={styles.gradient1} />
                <div className={styles.gradient2} />
            </div>
        </div>
    );
}
