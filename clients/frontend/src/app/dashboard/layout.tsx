'use client';

import { ReactNode, useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
    FiHome, FiPackage, FiDollarSign, FiMessageSquare,
    FiUser, FiLogOut, FiMenu, FiX, FiCreditCard
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
                    <Link href="/dashboard">
                        <span className="gradient-text" style={{ fontSize: '24px', fontWeight: 700 }}>
                            Ký Gửi Kho Đất
                        </span>
                    </Link>
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

                <button className={styles.logoutBtn} onClick={handleLogout}>
                    <FiLogOut size={20} />
                    <span>Đăng xuất</span>
                </button>
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
