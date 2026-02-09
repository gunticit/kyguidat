'use client';

import { useState, useEffect } from 'react';
import { FiUser, FiMail, FiPhone, FiLock, FiEdit2, FiDollarSign } from 'react-icons/fi';
import { userApi, authApi } from '@/lib/api';
import styles from './profile.module.css';

interface UserProfile {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    avatar: string | null;
    wallet?: {
        balance: number;
        frozen_balance: number;
    };
    social_accounts?: {
        google: boolean;
        facebook: boolean;
        zalo: boolean;
    };
}

export default function ProfilePage() {
    const [isEditingProfile, setIsEditingProfile] = useState(false);
    const [isEditingPassword, setIsEditingPassword] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);

    const [user, setUser] = useState<UserProfile | null>(null);
    const [profileForm, setProfileForm] = useState({
        name: '',
        phone: '',
    });
    const [passwordForm, setPasswordForm] = useState({
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
    });
    const [passwordErrors, setPasswordErrors] = useState<Record<string, string>>({});

    const API_URL = process.env.NEXT_PUBLIC_API_URL || 'https://api.khodat.com/api';

    useEffect(() => {
        loadProfile();
    }, []);

    const loadProfile = async () => {
        try {
            setLoading(true);
            const response = await userApi.getProfile();
            if (response.data.success || response.data.data) {
                const userData = response.data.data;
                setUser(userData);
                setProfileForm({
                    name: userData.name || '',
                    phone: userData.phone || '',
                });
                // Update localStorage
                localStorage.setItem('user', JSON.stringify(userData));
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleProfileSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setError(null);
        setSuccess(null);

        try {
            const response = await userApi.updateProfile({
                name: profileForm.name,
                phone: profileForm.phone || undefined,
            });

            if (response.data.success || response.data.data) {
                const updatedUser = response.data.data;
                setUser(prev => prev ? { ...prev, ...updatedUser } : updatedUser);
                setIsEditingProfile(false);
                setSuccess('Cập nhật thông tin thành công');
                // Update localStorage
                localStorage.setItem('user', JSON.stringify({ ...user, ...updatedUser }));
            } else {
                setError(response.data.message || 'Có lỗi xảy ra');
            }
        } catch (error: any) {
            console.error('Update profile error:', error);
            setError(error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật');
        } finally {
            setIsLoading(false);
        }
    };

    const handlePasswordSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setError(null);
        setSuccess(null);
        setPasswordErrors({});

        // Validate
        const errors: Record<string, string> = {};
        if (!passwordForm.current_password) errors.current_password = 'Vui lòng nhập mật khẩu hiện tại';
        if (!passwordForm.new_password) errors.new_password = 'Vui lòng nhập mật khẩu mới';
        if (passwordForm.new_password.length < 8) errors.new_password = 'Mật khẩu phải có ít nhất 8 ký tự';
        if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
            errors.new_password_confirmation = 'Mật khẩu xác nhận không khớp';
        }

        if (Object.keys(errors).length > 0) {
            setPasswordErrors(errors);
            setIsLoading(false);
            return;
        }

        try {
            const response = await userApi.updatePassword({
                current_password: passwordForm.current_password,
                new_password: passwordForm.new_password,
                new_password_confirmation: passwordForm.new_password_confirmation,
            });

            if (response.data.success) {
                setIsEditingPassword(false);
                setPasswordForm({ current_password: '', new_password: '', new_password_confirmation: '' });
                setSuccess('Đổi mật khẩu thành công');
            } else {
                setError(response.data.message || 'Có lỗi xảy ra');
            }
        } catch (error: any) {
            console.error('Update password error:', error);
            if (error.response?.data?.errors) {
                setPasswordErrors(error.response.data.errors);
            } else {
                setError(error.response?.data?.message || 'Mật khẩu hiện tại không đúng');
            }
        } finally {
            setIsLoading(false);
        }
    };

    const handleSocialLink = (provider: string) => {
        // Redirect to social auth
        window.location.href = `${API_URL}/auth/${provider}`;
    };

    if (loading) {
        return (
            <div className={styles.loading}>
                <div className={styles.spinner}></div>
                <p>Đang tải thông tin...</p>
            </div>
        );
    }

    return (
        <div>
            <h1 className={styles.pageTitle}>Tài khoản</h1>
            <p className={styles.pageSubtitle}>Quản lý thông tin cá nhân và bảo mật</p>

            {error && (
                <div className={styles.errorAlert}>{error}</div>
            )}
            {success && (
                <div className={styles.successAlert}>{success}</div>
            )}

            <div className={styles.container}>
                {/* Wallet Info */}
                {user?.wallet && (
                    <div className={`card ${styles.walletCard} ${styles.containerFullWidth}`}>
                        <div className={styles.walletInfo}>
                            <FiDollarSign size={24} />
                            <div>
                                <p className={styles.walletLabel}>Số dư ví</p>
                                <p className={styles.walletBalance}>
                                    {user.wallet.balance.toLocaleString('vi-VN')}đ
                                </p>
                            </div>
                        </div>
                        {user.wallet.frozen_balance > 0 && (
                            <p className={styles.frozenBalance}>
                                Tạm giữ: {user.wallet.frozen_balance.toLocaleString('vi-VN')}đ
                            </p>
                        )}
                    </div>
                )}

                {/* Profile Section */}
                <div className="card">
                    <div className={styles.sectionHeader}>
                        <h3 className={styles.sectionTitle}>Thông tin cá nhân</h3>
                        {!isEditingProfile && (
                            <button
                                className={styles.editBtn}
                                onClick={() => setIsEditingProfile(true)}
                            >
                                <FiEdit2 /> Chỉnh sửa
                            </button>
                        )}
                    </div>

                    {isEditingProfile ? (
                        <form onSubmit={handleProfileSubmit}>
                            <div className={styles.formGroup}>
                                <label className="label">Họ tên</label>
                                <input
                                    type="text"
                                    className="input"
                                    value={profileForm.name}
                                    onChange={(e) => setProfileForm(p => ({ ...p, name: e.target.value }))}
                                />
                            </div>
                            <div className={styles.formGroup}>
                                <label className="label">Email</label>
                                <input
                                    type="email"
                                    className="input"
                                    value={user?.email || ''}
                                    disabled
                                    style={{ opacity: 0.6 }}
                                />
                                <p className={styles.hint}>Email không thể thay đổi. Liên hệ hỗ trợ nếu cần.</p>
                            </div>
                            <div className={styles.formGroup}>
                                <label className="label">Số điện thoại</label>
                                <input
                                    type="tel"
                                    className="input"
                                    value={profileForm.phone}
                                    onChange={(e) => setProfileForm(p => ({ ...p, phone: e.target.value }))}
                                />
                            </div>
                            <div className={styles.formActions}>
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    onClick={() => {
                                        setIsEditingProfile(false);
                                        setProfileForm({
                                            name: user?.name || '',
                                            phone: user?.phone || '',
                                        });
                                    }}
                                >
                                    Hủy
                                </button>
                                <button type="submit" className="btn btn-primary" disabled={isLoading}>
                                    {isLoading ? <span className="spinner" /> : 'Lưu thay đổi'}
                                </button>
                            </div>
                        </form>
                    ) : (
                        <div className={styles.infoList}>
                            <div className={styles.infoItem}>
                                <FiUser className={styles.infoIcon} />
                                <div>
                                    <p className={styles.infoLabel}>Họ tên</p>
                                    <p className={styles.infoValue}>{user?.name || 'Chưa cập nhật'}</p>
                                </div>
                            </div>
                            <div className={styles.infoItem}>
                                <FiMail className={styles.infoIcon} />
                                <div>
                                    <p className={styles.infoLabel}>Email</p>
                                    <p className={styles.infoValue}>{user?.email}</p>
                                </div>
                            </div>
                            <div className={styles.infoItem}>
                                <FiPhone className={styles.infoIcon} />
                                <div>
                                    <p className={styles.infoLabel}>Số điện thoại</p>
                                    <p className={styles.infoValue}>{user?.phone || 'Chưa cập nhật'}</p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Password Section */}
                <div className="card">
                    <div className={styles.sectionHeader}>
                        <h3 className={styles.sectionTitle}>Đổi mật khẩu</h3>
                        {!isEditingPassword && (
                            <button
                                className={styles.editBtn}
                                onClick={() => setIsEditingPassword(true)}
                            >
                                <FiLock /> Đổi mật khẩu
                            </button>
                        )}
                    </div>

                    {isEditingPassword ? (
                        <form onSubmit={handlePasswordSubmit}>
                            <div className={styles.formGroup}>
                                <label className="label">Mật khẩu hiện tại</label>
                                <input
                                    type="password"
                                    className={`input ${passwordErrors.current_password ? 'input-error' : ''}`}
                                    value={passwordForm.current_password}
                                    onChange={(e) => setPasswordForm(p => ({ ...p, current_password: e.target.value }))}
                                />
                                {passwordErrors.current_password && (
                                    <p className="error-text">{passwordErrors.current_password}</p>
                                )}
                            </div>
                            <div className={styles.formGroup}>
                                <label className="label">Mật khẩu mới</label>
                                <input
                                    type="password"
                                    className={`input ${passwordErrors.new_password ? 'input-error' : ''}`}
                                    value={passwordForm.new_password}
                                    onChange={(e) => setPasswordForm(p => ({ ...p, new_password: e.target.value }))}
                                />
                                {passwordErrors.new_password && (
                                    <p className="error-text">{passwordErrors.new_password}</p>
                                )}
                            </div>
                            <div className={styles.formGroup}>
                                <label className="label">Xác nhận mật khẩu mới</label>
                                <input
                                    type="password"
                                    className={`input ${passwordErrors.new_password_confirmation ? 'input-error' : ''}`}
                                    value={passwordForm.new_password_confirmation}
                                    onChange={(e) => setPasswordForm(p => ({ ...p, new_password_confirmation: e.target.value }))}
                                />
                                {passwordErrors.new_password_confirmation && (
                                    <p className="error-text">{passwordErrors.new_password_confirmation}</p>
                                )}
                            </div>
                            <div className={styles.formActions}>
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    onClick={() => {
                                        setIsEditingPassword(false);
                                        setPasswordForm({ current_password: '', new_password: '', new_password_confirmation: '' });
                                        setPasswordErrors({});
                                    }}
                                >
                                    Hủy
                                </button>
                                <button type="submit" className="btn btn-primary" disabled={isLoading}>
                                    {isLoading ? <span className="spinner" /> : 'Đổi mật khẩu'}
                                </button>
                            </div>
                        </form>
                    ) : (
                        <p className={styles.passwordHint}>
                            Mật khẩu của bạn nên có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số.
                        </p>
                    )}
                </div>

                {/* Connected Accounts */}
                <div className="card">
                    <h3 className={styles.sectionTitle}>Tài khoản liên kết</h3>
                    <div className={styles.connectedList}>
                        <div className={styles.connectedItem}>
                            <div className={styles.connectedInfo}>
                                <span className={styles.connectedIcon}>🔵</span>
                                <div>
                                    <p className={styles.connectedName}>Google</p>
                                    <p className={styles.connectedStatus}>
                                        {user?.social_accounts?.google ? 'Đã liên kết' : 'Chưa liên kết'}
                                    </p>
                                </div>
                            </div>
                            <button
                                className={`btn ${user?.social_accounts?.google ? 'btn-secondary' : 'btn-outline'}`}
                                style={{ padding: '8px 16px', fontSize: '13px' }}
                                onClick={() => handleSocialLink('google')}
                            >
                                {user?.social_accounts?.google ? 'Hủy liên kết' : 'Liên kết'}
                            </button>
                        </div>
                        {/* <div className={styles.connectedItem}>
                            <div className={styles.connectedInfo}>
                                <span className={styles.connectedIcon}>🔷</span>
                                <div>
                                    <p className={styles.connectedName}>Facebook</p>
                                    <p className={styles.connectedStatus} style={{ color: user?.social_accounts?.facebook ? undefined : 'var(--text-secondary)' }}>
                                        {user?.social_accounts?.facebook ? 'Đã liên kết' : 'Chưa liên kết'}
                                    </p>
                                </div>
                            </div>
                            <button
                                className={`btn ${user?.social_accounts?.facebook ? 'btn-secondary' : 'btn-outline'}`}
                                style={{ padding: '8px 16px', fontSize: '13px' }}
                                onClick={() => handleSocialLink('facebook')}
                            >
                                {user?.social_accounts?.facebook ? 'Hủy liên kết' : 'Liên kết'}
                            </button>
                        </div>
                        <div className={styles.connectedItem}>
                            <div className={styles.connectedInfo}>
                                <span className={styles.connectedIcon}>🔷</span>
                                <div>
                                    <p className={styles.connectedName}>Zalo</p>
                                    <p className={styles.connectedStatus} style={{ color: user?.social_accounts?.zalo ? undefined : 'var(--text-secondary)' }}>
                                        {user?.social_accounts?.zalo ? 'Đã liên kết' : 'Chưa liên kết'}
                                    </p>
                                </div>
                            </div>
                            <button
                                className={`btn ${user?.social_accounts?.zalo ? 'btn-secondary' : 'btn-outline'}`}
                                style={{ padding: '8px 16px', fontSize: '13px' }}
                                onClick={() => handleSocialLink('zalo')}
                            >
                                {user?.social_accounts?.zalo ? 'Hủy liên kết' : 'Liên kết'}
                            </button>
                        </div> */}
                    </div>
                </div>
            </div>
        </div>
    );
}
