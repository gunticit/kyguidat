'use client';

import { useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';

function AuthCallbackContent() {
    const router = useRouter();
    const searchParams = useSearchParams();

    useEffect(() => {
        const token = searchParams.get('token');
        const error = searchParams.get('error');

        if (error) {
            router.push(`/login?error=${error}`);
            return;
        }

        if (token) {
            // Save token to localStorage
            localStorage.setItem('auth_token', token);
            router.push('/dashboard');
        } else {
            router.push('/login?error=no_token');
        }
    }, [searchParams, router]);

    return (
        <div style={{
            minHeight: '100vh',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            flexDirection: 'column',
            gap: '16px',
        }}>
            <div className="spinner" style={{ width: '48px', height: '48px' }} />
            <p style={{ color: 'var(--text-secondary)' }}>Đang xử lý đăng nhập...</p>
        </div>
    );
}

export default function AuthCallbackPage() {
    return (
        <Suspense fallback={
            <div style={{
                minHeight: '100vh',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                flexDirection: 'column',
                gap: '16px',
            }}>
                <div className="spinner" style={{ width: '48px', height: '48px' }} />
                <p style={{ color: 'var(--text-secondary)' }}>Đang tải...</p>
            </div>
        }>
            <AuthCallbackContent />
        </Suspense>
    );
}
