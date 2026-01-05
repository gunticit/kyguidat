'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { FcGoogle } from 'react-icons/fc';
import { FaFacebook } from 'react-icons/fa';
import { SiZalo } from 'react-icons/si';
import { authApi } from '@/lib/api';
import styles from './login.module.css';

export default function LoginPage() {
    const router = useRouter();
    const [isLoading, setIsLoading] = useState(false);
    const [formData, setFormData] = useState({
        email: '',
        password: '',
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
        if (!formData.email) newErrors.email = 'Email là bắt buộc';
        if (!formData.password) newErrors.password = 'Mật khẩu là bắt buộc';

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            setIsLoading(false);
            return;
        }

        try {
            const response = await authApi.login(formData);

            if (response.data.success) {
                // Lưu token vào localStorage
                localStorage.setItem('auth_token', response.data.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.data.user));

                // Redirect đến dashboard
                router.push('/dashboard');
            } else {
                setGeneralError(response.data.message || 'Đăng nhập thất bại');
            }
        } catch (error: unknown) {
            console.error('Login error:', error);

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


    const handleSocialLogin = (provider: 'google' | 'facebook' | 'zalo') => {
        const urls = {
            google: process.env.NEXT_PUBLIC_GOOGLE_LOGIN_URL,
            facebook: process.env.NEXT_PUBLIC_FACEBOOK_LOGIN_URL,
            zalo: process.env.NEXT_PUBLIC_ZALO_LOGIN_URL,
        };
        window.location.href = urls[provider] || '#';
    };

    return (
        <div className={styles.container}>
            <div className={styles.formWrapper}>
                <div className={styles.header}>
                    <h1 className={styles.title}>Đăng nhập</h1>
                    <p className={styles.subtitle}>Chào mừng bạn quay trở lại</p>
                </div>

                {/* Social Login */}
                <div className={styles.socialButtons}>
                    <button
                        type="button"
                        className={styles.socialBtn}
                        onClick={() => handleSocialLogin('google')}
                    >
                        <FcGoogle size={20} />
                        <span>Google</span>
                    </button>
                    <button
                        type="button"
                        className={styles.socialBtn}
                        onClick={() => handleSocialLogin('facebook')}
                    >
                        <FaFacebook size={20} color="#1877F2" />
                        <span>Facebook</span>
                    </button>
                    <button
                        type="button"
                        className={styles.socialBtn}
                        onClick={() => handleSocialLogin('zalo')}
                    >
                        <SiZalo size={20} color="#0068FF" />
                        <span>Zalo</span>
                    </button>
                </div>

                <div className={styles.divider}>
                    <span>hoặc đăng nhập bằng email</span>
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

                {/* Login Form */}
                <form onSubmit={handleSubmit} className={styles.form}>
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

                    <div className={styles.forgotPassword}>
                        <Link href="/forgot-password">Quên mật khẩu?</Link>
                    </div>

                    <button
                        type="submit"
                        className="btn btn-primary"
                        style={{ width: '100%' }}
                        disabled={isLoading}
                    >
                        {isLoading ? <span className="spinner" /> : 'Đăng nhập'}
                    </button>
                </form>

                <p className={styles.registerLink}>
                    Chưa có tài khoản? <Link href="/register">Đăng ký ngay</Link>
                </p>
            </div>

            {/* Decorative Background */}
            <div className={styles.decorative}>
                <div className={styles.gradient1} />
                <div className={styles.gradient2} />
            </div>
        </div>
    );
}
