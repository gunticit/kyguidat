'use client';

export default function GlobalError({
    error,
    reset,
}: {
    error: Error & { digest?: string };
    reset: () => void;
}) {
    return (
        <html>
            <body>
                <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '100vh', fontFamily: 'system-ui, sans-serif' }}>
                    <div style={{ textAlign: 'center', padding: '40px' }}>
                        <h2 style={{ marginBottom: '16px' }}>Đã xảy ra lỗi</h2>
                        <p style={{ color: '#666', marginBottom: '24px' }}>{error.message || 'Vui lòng thử lại'}</p>
                        <button
                            onClick={() => reset()}
                            style={{ padding: '10px 24px', background: '#088b0c', color: 'white', border: 'none', borderRadius: '8px', cursor: 'pointer', fontSize: '16px' }}
                        >
                            Thử lại
                        </button>
                    </div>
                </div>
            </body>
        </html>
    );
}
