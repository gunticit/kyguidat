'use client';

import { useState } from 'react';
import Link from 'next/link';
import { FiArrowLeft, FiMail } from 'react-icons/fi';
import { authApi } from '@/lib/api';
import styles from '../login/login.module.css';

export default function ForgotPasswordPage() {
    const [email, setEmail] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setSuccess(false);

        if (!email) {
            setError('Vui lòng nhập email');
            return;
        }

        try {
            setIsLoading(true);
            const response = await authApi.forgotPassword(email);

            if (response.data.success) {
                setSuccess(true);
            } else {
                setError(response.data.message || 'Có lỗi xảy ra');
            }
        } catch (err: unknown) {
            interface AxiosError {
                response?: {
                    data?: {
                        message?: string;
                    };
                };
            }
            const axiosError = err as AxiosError;
            setError(
                axiosError.response?.data?.message ||
                'Có lỗi xảy ra. Vui lòng thử lại sau.'
            );
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className={styles.container}>
            <div className={styles.formWrapper}>
                <Link href="/login" className={styles.backLink}>
                    <FiArrowLeft size={16} />
                    Quay lại đăng nhập
                </Link>

                <div className={styles.formCard}>
                    <div className={styles.brand}>
                        <span className={styles.brandName}>Ký Gửi Kho Đất</span>
                    </div>

                    <div className={styles.header}>
                        <h1 className={styles.title}>Quên mật khẩu</h1>
                        <p className={styles.subtitle}>
                            Nhập email đã đăng ký, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu cho bạn.
                        </p>
                    </div>

                    {error && (
                        <div className={styles.errorBox}>{error}</div>
                    )}

                    {success ? (
                        <div className={styles.successBox}>
                            <FiMail size={20} style={{ marginBottom: 8, display: 'block', margin: '0 auto 8px' }} />
                            Chúng tôi đã gửi email hướng dẫn đặt lại mật khẩu đến <strong>{email}</strong>. Vui lòng kiểm tra hộp thư (bao gồm thư rác).
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit} className={styles.form}>
                            <div className={styles.formGroup}>
                                <label htmlFor="email">Email</label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    placeholder="your@email.com"
                                    value={email}
                                    onChange={(e) => {
                                        setEmail(e.target.value);
                                        setError('');
                                    }}
                                    autoFocus
                                />
                            </div>

                            <button
                                type="submit"
                                className={styles.submitBtn}
                                disabled={isLoading}
                            >
                                {isLoading ? <span className={styles.spinner} /> : 'Gửi email đặt lại mật khẩu'}
                            </button>
                        </form>
                    )}
                </div>

                <p className={styles.bottomLink}>
                    Nhớ mật khẩu? <Link href="/login">Đăng nhập</Link>
                </p>
            </div>

            <div className={styles.decorative}>
                <div className={styles.gradient1} />
                <div className={styles.gradient2} />
            </div>
        </div>
    );
}
