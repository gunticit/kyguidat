'use client';

import { Suspense, useEffect, useState } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { FiCheckCircle, FiXCircle, FiAlertTriangle, FiArrowLeft } from 'react-icons/fi';
import Link from 'next/link';

const responseMessages: Record<string, string> = {
    '00': 'Giao dịch thành công',
    '07': 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường)',
    '09': 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking',
    '10': 'Xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
    '11': 'Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch',
    '12': 'Thẻ/Tài khoản bị khóa',
    '13': 'Quý khách nhập sai mật khẩu xác thực giao dịch (OTP)',
    '24': 'Giao dịch đã bị hủy bởi khách hàng',
    '51': 'Tài khoản không đủ số dư để thực hiện giao dịch',
    '65': 'Tài khoản đã vượt quá hạn mức giao dịch trong ngày',
    '75': 'Ngân hàng thanh toán đang bảo trì',
    '79': 'Nhập sai mật khẩu thanh toán quá số lần quy định',
    '99': 'Lỗi không xác định',
};

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
}

function CallbackContent() {
    const searchParams = useSearchParams();
    const router = useRouter();
    const [countdown, setCountdown] = useState(10);

    const responseCode = searchParams.get('vnp_ResponseCode') || '';
    const transactionStatus = searchParams.get('vnp_TransactionStatus') || '';
    const amount = parseInt(searchParams.get('vnp_Amount') || '0') / 100;
    const txnRef = searchParams.get('vnp_TxnRef') || '';
    const bankCode = searchParams.get('vnp_BankCode') || '';
    const payDate = searchParams.get('vnp_PayDate') || '';

    const isSuccess = responseCode === '00' && transactionStatus === '00';
    const isCancelled = responseCode === '24';
    const message = responseMessages[responseCode] || 'Giao dịch không thành công';

    const formattedDate = payDate
        ? `${payDate.slice(6, 8)}/${payDate.slice(4, 6)}/${payDate.slice(0, 4)} ${payDate.slice(8, 10)}:${payDate.slice(10, 12)}:${payDate.slice(12, 14)}`
        : '';

    useEffect(() => {
        const timer = setInterval(() => {
            setCountdown(prev => {
                if (prev <= 1) {
                    clearInterval(timer);
                    router.push('/dashboard/deposit');
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
        return () => clearInterval(timer);
    }, [router]);

    return (
        <div style={{ maxWidth: 560, margin: '0 auto', padding: '40px 20px' }}>
            <div style={{
                background: 'var(--card)',
                borderRadius: 16,
                padding: '40px 32px',
                textAlign: 'center',
                border: '1px solid var(--border)',
            }}>
                {isSuccess ? (
                    <FiCheckCircle size={64} color="#22c55e" style={{ marginBottom: 20 }} />
                ) : isCancelled ? (
                    <FiAlertTriangle size={64} color="#f59e0b" style={{ marginBottom: 20 }} />
                ) : (
                    <FiXCircle size={64} color="#ef4444" style={{ marginBottom: 20 }} />
                )}

                <h1 style={{
                    fontSize: 24,
                    fontWeight: 700,
                    marginBottom: 8,
                    color: isSuccess ? '#22c55e' : isCancelled ? '#f59e0b' : '#ef4444',
                }}>
                    {isSuccess ? 'Thanh toán thành công!' : isCancelled ? 'Giao dịch đã hủy' : 'Thanh toán thất bại'}
                </h1>

                <p style={{ color: 'var(--text-secondary)', marginBottom: 24, fontSize: 14 }}>
                    {message}
                </p>

                <div style={{
                    background: 'var(--background)',
                    borderRadius: 12,
                    padding: 20,
                    textAlign: 'left',
                    marginBottom: 24,
                }}>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                        {amount > 0 && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14 }}>
                                <span style={{ color: 'var(--text-secondary)' }}>Số tiền</span>
                                <strong style={{ color: isSuccess ? '#22c55e' : 'var(--text)' }}>
                                    {formatCurrency(amount)}
                                </strong>
                            </div>
                        )}
                        {txnRef && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14 }}>
                                <span style={{ color: 'var(--text-secondary)' }}>Mã giao dịch</span>
                                <span style={{ fontFamily: 'monospace', fontSize: 13 }}>{txnRef}</span>
                            </div>
                        )}
                        {bankCode && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14 }}>
                                <span style={{ color: 'var(--text-secondary)' }}>Ngân hàng</span>
                                <span>{bankCode}</span>
                            </div>
                        )}
                        {formattedDate && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14 }}>
                                <span style={{ color: 'var(--text-secondary)' }}>Thời gian</span>
                                <span>{formattedDate}</span>
                            </div>
                        )}
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14 }}>
                            <span style={{ color: 'var(--text-secondary)' }}>Mã phản hồi</span>
                            <span>{responseCode}</span>
                        </div>
                    </div>
                </div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                    <Link
                        href="/dashboard/deposit"
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            gap: 8,
                            padding: '12px 24px',
                            background: 'var(--primary)',
                            color: 'white',
                            borderRadius: 10,
                            textDecoration: 'none',
                            fontWeight: 600,
                            fontSize: 14,
                        }}
                    >
                        <FiArrowLeft size={16} />
                        Quay lại trang nạp tiền
                    </Link>

                    <p style={{ fontSize: 12, color: 'var(--text-secondary)' }}>
                        Tự động chuyển hướng sau {countdown} giây...
                    </p>
                </div>
            </div>
        </div>
    );
}

export default function VnpayCallbackPage() {
    return (
        <Suspense fallback={
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '60vh' }}>
                <p>Đang xử lý kết quả thanh toán...</p>
            </div>
        }>
            <CallbackContent />
        </Suspense>
    );
}
