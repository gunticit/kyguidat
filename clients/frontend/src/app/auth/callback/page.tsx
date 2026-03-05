'use client';

import { useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

function AuthCallbackContent() {
    const router = useRouter();
    const searchParams = useSearchParams();

    useEffect(() => {
        const token = searchParams.get('token');
        const error = searchParams.get('error');
        const redirectTo = searchParams.get('redirect') || '/dashboard';

        if (error) {
            router.push(`/login?error=${error}`);
            return;
        }

        if (token) {
            // Save token to localStorage
            localStorage.setItem('auth_token', token);

            // Fetch user info immediately after social login
            fetch(`${API_URL}/auth/me`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data) {
                        localStorage.setItem('user', JSON.stringify(data.data));
                    }
                    router.push(redirectTo);
                })
                .catch(() => {
                    // Even if fetching user fails, still redirect (token is saved)
                    router.push(redirectTo);
                });
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
