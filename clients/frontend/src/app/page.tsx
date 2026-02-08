import Link from 'next/link';

export default function Home() {
    return (
        <main className="min-h-screen" style={{ minHeight: '100vh' }}>
            {/* Hero Section */}
            <section style={{
                minHeight: '100vh',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center',
                alignItems: 'center',
                textAlign: 'center',
                padding: '40px 20px',
                background: 'radial-gradient(ellipse at top, rgba(99, 102, 241, 0.15) 0%, transparent 50%)',
            }}>
                <h1 style={{
                    fontSize: 'clamp(2.5rem, 5vw, 4rem)',
                    fontWeight: 800,
                    marginBottom: '20px',
                    lineHeight: 1.2,
                }}>
                    Nền tảng <span className="gradient-text">Ký gửi</span>
                    <br />An toàn & Uy tín
                </h1>

                <p style={{
                    fontSize: '18px',
                    color: 'var(--text-secondary)',
                    maxWidth: '600px',
                    marginBottom: '40px',
                    lineHeight: 1.6,
                }}>
                    Ký gửi sản phẩm dễ dàng, nạp tiền nhanh chóng qua VNPay, Momo hoặc chuyển khoản.
                    Đội ngũ hỗ trợ 24/7.
                </p>

                <div style={{ display: 'flex', gap: '16px', flexWrap: 'wrap', justifyContent: 'center' }}>
                    <Link href="/login" className="btn btn-primary" style={{ minWidth: '160px' }}>
                        Đăng nhập
                    </Link>
                    <Link href="/register" className="btn btn-outline" style={{ minWidth: '160px' }}>
                        Đăng ký ngay
                    </Link>
                </div>

                {/* Features */}
                <div style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
                    gap: '24px',
                    marginTop: '80px',
                    maxWidth: '1200px',
                    width: '100%',
                }}>
                    <div className="card animate-fadeIn" style={{ animationDelay: '0.1s' }}>
                        <div style={{
                            width: '48px',
                            height: '48px',
                            borderRadius: '12px',
                            background: 'linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            marginBottom: '16px',
                            fontSize: '24px',
                        }}>
                            📦
                        </div>
                        <h3 style={{ fontSize: '18px', fontWeight: 600, marginBottom: '8px' }}>Ký gửi dễ dàng</h3>
                        <p style={{ color: 'var(--text-secondary)', fontSize: '14px' }}>
                            Đăng sản phẩm ký gửi chỉ trong vài phút, theo dõi trạng thái realtime
                        </p>
                    </div>

                    <div className="card animate-fadeIn" style={{ animationDelay: '0.2s' }}>
                        <div style={{
                            width: '48px',
                            height: '48px',
                            borderRadius: '12px',
                            background: 'linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            marginBottom: '16px',
                            fontSize: '24px',
                        }}>
                            💳
                        </div>
                        <h3 style={{ fontSize: '18px', fontWeight: 600, marginBottom: '8px' }}>Nạp tiền linh hoạt</h3>
                        <p style={{ color: 'var(--text-secondary)', fontSize: '14px' }}>
                            Hỗ trợ VNPay, Momo và chuyển khoản ngân hàng
                        </p>
                    </div>

                    <div className="card animate-fadeIn" style={{ animationDelay: '0.3s' }}>
                        <div style={{
                            width: '48px',
                            height: '48px',
                            borderRadius: '12px',
                            background: 'linear-gradient(135deg, var(--accent) 0%, #d97706 100%)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            marginBottom: '16px',
                            fontSize: '24px',
                        }}>
                            🛡️
                        </div>
                        <h3 style={{ fontSize: '18px', fontWeight: 600, marginBottom: '8px' }}>Hỗ trợ 24/7</h3>
                        <p style={{ color: 'var(--text-secondary)', fontSize: '14px' }}>
                            Đội ngũ hỗ trợ luôn sẵn sàng giải đáp mọi thắc mắc của bạn
                        </p>
                    </div>
                </div>
            </section>
        </main>
    );
}
