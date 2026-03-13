'use client';

import { ReactNode, useState, useEffect } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { usePathname } from 'next/navigation';
import {
    FiHome, FiPackage, FiDollarSign, FiMessageSquare,
    FiUser, FiLogOut, FiMenu, FiX, FiCreditCard, FiSun, FiMoon
} from 'react-icons/fi';
import { authApi } from '@/lib/api';
import styles from './dashboard.module.css';

const navItems = [
    { href: '/dashboard', icon: FiHome, label: 'Tổng quan' },
    { href: '/dashboard/consignments', icon: FiPackage, label: 'Ký gửi' },
    { href: '/dashboard/packages', icon: FiCreditCard, label: 'Gói đăng bài' },
    { href: '/dashboard/deposit', icon: FiDollarSign, label: 'Nạp tiền' },
    { href: '/dashboard/support', icon: FiMessageSquare, label: 'Hỗ trợ' },
    { href: '/dashboard/profile', icon: FiUser, label: 'Tài khoản' },
];

export default function DashboardLayout({ children }: { children: ReactNode }) {
    const pathname = usePathname();
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [user, setUser] = useState<{ name?: string; email?: string; avatar?: string } | null>(null);
    const [theme, setTheme] = useState('dark');

    // Theme initialization
    useEffect(() => {
        const savedTheme = localStorage.getItem('app-theme') || 'dark';
        setTheme(savedTheme);
        document.documentElement.setAttribute('data-theme', savedTheme);
    }, []);

    const toggleTheme = () => {
        const newTheme = theme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
        localStorage.setItem('app-theme', newTheme);
        document.documentElement.setAttribute('data-theme', newTheme);
    };

    useEffect(() => {
        const loadUser = async () => {
            try {
                const storedUser = localStorage.getItem('user');
                const token = localStorage.getItem('auth_token');

                if (storedUser) {
                    setUser(JSON.parse(storedUser));
                }

                // If we have a token but no user data (e.g. just after social login),
                // or user data might be stale, fetch fresh data
                if (token && !storedUser) {
                    const res = await authApi.me();
                    if (res.data?.success && res.data?.data) {
                        const userData = res.data.data;
                        localStorage.setItem('user', JSON.stringify(userData));
                        setUser(userData);
                    }
                }
            } catch (e) {
                console.error('Failed to load user', e);
            }
        };
        loadUser();

        // Listen for storage changes (profile updates)
        const handleStorageChange = () => {
            try {
                const storedUser = localStorage.getItem('user');
                if (storedUser) setUser(JSON.parse(storedUser));
            } catch (e) { }
        };
        window.addEventListener('storage', handleStorageChange);

        // Also listen for custom event (same-tab updates)
        window.addEventListener('userUpdated', handleStorageChange);
        return () => {
            window.removeEventListener('storage', handleStorageChange);
            window.removeEventListener('userUpdated', handleStorageChange);
        };
    }, []);

    const handleLogout = async () => {
        try {
            await authApi.logout();
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }
    };


    return (
        <div className={styles.layout}>
            {/* Mobile Menu Button */}
            <button
                className={styles.mobileMenuBtn}
                onClick={() => setIsSidebarOpen(!isSidebarOpen)}
            >
                {isSidebarOpen ? <FiX size={24} /> : <FiMenu size={24} />}
            </button>

            {/* Sidebar */}
            <aside className={`${styles.sidebar} ${isSidebarOpen ? styles.sidebarOpen : ''}`}>
            <div className={styles.logo}>
                    <Link href="/dashboard" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                        <Image
                            src="/logo.jpg"
                            alt="Kho Đất"
                            width={40}
                            height={40}
                            style={{ borderRadius: '8px' }}
                        />
                        <span className="gradient-text" style={{ fontSize: '18px', fontWeight: 700 }}>
                            Kho Đất
                        </span>
                    </Link>
                </div>

                {/* User Info */}
                <div className={styles.userInfo}>
                    <div className={styles.userAvatar}>
                        {user?.avatar && user.avatar !== 'null' && user.avatar.startsWith('http') ? (
                            <Image
                                src={user.avatar}
                                alt={user.name || 'Avatar'}
                                width={40}
                                height={40}
                                className={styles.avatarImg}
                            />
                        ) : (
                            <div className={styles.avatarFallback}>
                                {user?.name?.charAt(0)?.toUpperCase() || 'U'}
                            </div>
                        )}
                    </div>
                    <div className={styles.userDetails}>
                        <p className={styles.userName}>{user?.name || 'Người dùng'}</p>
                        <p className={styles.userEmail}>{user?.email || ''}</p>
                    </div>
                </div>

                <nav className={styles.nav}>
                    {navItems.map((item) => {
                        const isActive = pathname === item.href ||
                            (item.href !== '/dashboard' && pathname.startsWith(item.href));
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`${styles.navItem} ${isActive ? styles.navItemActive : ''}`}
                                onClick={() => setIsSidebarOpen(false)}
                            >
                                <item.icon size={20} />
                                <span>{item.label}</span>
                            </Link>
                        );
                    })}
                </nav>

                <div className={styles.sidebarFooter}>
                    <button className={styles.themeToggle} onClick={toggleTheme}>
                        {theme === 'dark' ? <FiSun size={18} /> : <FiMoon size={18} />}
                        <span>{theme === 'dark' ? 'Chế độ sáng' : 'Chế độ tối'}</span>
                    </button>
                    <button className={styles.logoutBtn} onClick={handleLogout}>
                        <FiLogOut size={20} />
                        <span>Đăng xuất</span>
                    </button>
                </div>
            </aside>

            {/* Overlay for mobile */}
            {isSidebarOpen && (
                <div
                    className={styles.overlay}
                    onClick={() => setIsSidebarOpen(false)}
                />
            )}

            {/* Main Content */}
            <main className={styles.main}>
                {children}
            </main>
        </div>
    );
}
