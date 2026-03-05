'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';

export default function NotFound() {
    const router = useRouter();
    const [countdown, setCountdown] = useState(4);

    useEffect(() => {
        const timer = setInterval(() => {
            setCountdown((prev) => {
                if (prev <= 1) {
                    clearInterval(timer);
                    router.push('/');
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
        return () => clearInterval(timer);
    }, [router]);

    return (
        <div style={{
            minHeight: '100vh',
            background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: '20px',
        }}>
            <div style={{ textAlign: 'center', maxWidth: '420px' }}>
                {/* 404 Number */}
                <div style={{ position: 'relative', marginBottom: '24px' }}>
                    <span style={{
                        fontSize: 'clamp(100px, 20vw, 160px)',
                        fontWeight: 900,
                        color: '#1e293b',
                        lineHeight: 1,
                        userSelect: 'none',
                        display: 'block',
                    }}>
                        404
                    </span>
                    <div style={{
                        position: 'absolute',
                        inset: 0,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                    }}>
                        <svg width="80" height="80" fill="none" stroke="#4ade80" viewBox="0 0 24 24"
                            style={{ opacity: 0.6 }}>
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {/* Message */}
                <h1 style={{
                    fontSize: '28px',
                    fontWeight: 700,
                    color: '#f1f5f9',
                    marginBottom: '12px',
                }}>
                    Trang không tồn tại
                </h1>
                <p style={{
                    color: '#94a3b8',
                    fontSize: '16px',
                    marginBottom: '24px',
                    lineHeight: 1.6,
                }}>
                    Trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.
                </p>

                {/* Countdown */}
                <p style={{
                    color: '#64748b',
                    fontSize: '14px',
                    marginBottom: '24px',
                }}>
                    Tự động chuyển hướng sau{' '}
                    <span style={{ color: '#4ade80', fontWeight: 700 }}>{countdown}</span> giây...
                </p>

                {/* Button */}
                <a
                    href="/"
                    className="btn btn-primary"
                    style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '8px',
                        padding: '14px 28px',
                        background: 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
                        color: 'white',
                        borderRadius: '12px',
                        fontWeight: 600,
                        fontSize: '15px',
                        textDecoration: 'none',
                        boxShadow: '0 4px 14px rgba(34, 197, 94, 0.4)',
                        transition: 'all 0.2s ease',
                    }}
                >
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Về trang chủ
                </a>
            </div>
        </div>
    );
}
