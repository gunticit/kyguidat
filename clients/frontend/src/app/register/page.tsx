'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { FcGoogle } from 'react-icons/fc';
import { FaFacebook } from 'react-icons/fa';
import { SiZalo } from 'react-icons/si';
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

    useEffect(() => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            router.push('/dashboard');
        }
    }, [router]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
        setErrors(prev => ({ ...prev, [e.target.name]: '' }));
        setGeneralError('');
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setGeneralError('');

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
                localStorage.setItem('auth_token', response.data.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.data.user));
                router.push('/dashboard');
            } else {
                setGeneralError(response.data.message || 'Đăng ký thất bại');
            }
        } catch (error: unknown) {
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
                <div className={styles.formCard}>
                    <div className={styles.brand}>
                        <span className={styles.brandName}>Ký Gửi Kho Đất</span>
                    </div>

                    <div className={styles.header}>
                        <h1 className={styles.title}>Tạo tài khoản</h1>
                        <p className={styles.subtitle}>Đăng ký để bắt đầu ký gửi bất động sản</p>
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
                        <span>hoặc đăng ký bằng email</span>
                    </div>

                    {generalError && (
                        <div className={styles.errorBox}>{generalError}</div>
                    )}

                    <form onSubmit={handleSubmit} className={styles.form}>
                        <div className={styles.formGroup}>
                            <label htmlFor="name">Họ tên</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                placeholder="Nguyễn Văn A"
                                value={formData.name}
                                onChange={handleChange}
                            />
                            {errors.name && <span className={styles.errorText}>{errors.name}</span>}
                        </div>

                        <div className={styles.formGroup}>
                            <label htmlFor="email">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                placeholder="your@email.com"
                                value={formData.email}
                                onChange={handleChange}
                            />
                            {errors.email && <span className={styles.errorText}>{errors.email}</span>}
                        </div>

                        <div className={styles.formGroup}>
                            <label htmlFor="phone">Số điện thoại (tùy chọn)</label>
                            <input
                                id="phone"
                                type="tel"
                                name="phone"
                                placeholder="0901234567"
                                value={formData.phone}
                                onChange={handleChange}
                            />
                        </div>

                        <div className={styles.formGroup}>
                            <label htmlFor="password">Mật khẩu</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Tối thiểu 6 ký tự"
                                value={formData.password}
                                onChange={handleChange}
                            />
                            {errors.password && <span className={styles.errorText}>{errors.password}</span>}
                        </div>

                        <div className={styles.formGroup}>
                            <label htmlFor="password_confirmation">Xác nhận mật khẩu</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Nhập lại mật khẩu"
                                value={formData.password_confirmation}
                                onChange={handleChange}
                            />
                            {errors.password_confirmation && <span className={styles.errorText}>{errors.password_confirmation}</span>}
                        </div>

                        <button
                            type="submit"
                            className={styles.submitBtn}
                            disabled={isLoading}
                        >
                            {isLoading ? <span className={styles.spinner} /> : 'Đăng ký'}
                        </button>
                    </form>
                </div>

                <p className={styles.bottomLink}>
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
