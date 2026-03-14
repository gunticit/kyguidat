'use client';

import { useState, useEffect } from 'react';
import { FiCreditCard, FiSmartphone, FiDollarSign, FiCopy, FiCheck, FiRefreshCw, FiPhone } from 'react-icons/fi';
import { paymentApi, userApi } from '@/lib/api';
import styles from './deposit.module.css';

interface Payment {
    id: number;
    amount: number;
    method: string;
    status: string;
    created_at: string;
}

interface BankInfo {
    bank_name: string;
    account_number: string;
    account_name: string;
    branch: string;
}

const paymentMethods = [
    { id: 'bank', name: 'Chuyển khoản thủ công', icon: FiDollarSign, description: 'Cần chờ admin xác nhận', disabled: false },
    { id: 'sepay', name: 'QR Code (Tự động)', icon: FiSmartphone, description: 'Xác nhận tự động trong 1 phút', disabled: false },
];

const quickAmounts = [50000, 100000, 200000, 500000, 1000000, 2000000];

const getStatusBadge = (status: string) => {
    const statusMap: Record<string, { label: string; class: string }> = {
        pending: { label: 'Đang xử lý', class: 'badge-pending' },
        completed: { label: 'Thành công', class: 'badge-success' },
        failed: { label: 'Thất bại', class: 'badge-error' },
        cancelled: { label: 'Đã hủy', class: 'badge-error' },
    };
    return statusMap[status] || { label: status, class: 'badge-info' };
};

const getMethodLabel = (method: string) => {
    const methodMap: Record<string, string> = {
        vnpay: 'VNPay',
        momo: 'Momo',
        sepay: 'QR Tự động',
        bank_transfer: 'Chuyển khoản',
    };
    return methodMap[method] || method;
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

export default function DepositPage() {
    const [selectedMethod, setSelectedMethod] = useState('bank');
    const [amount, setAmount] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [copied, setCopied] = useState<string | null>(null);
    const [payments, setPayments] = useState<Payment[]>([]);
    const [bankInfo, setBankInfo] = useState<BankInfo | null>(null);
    const [loadingHistory, setLoadingHistory] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [userPhone, setUserPhone] = useState('');
    const [showSepayQr, setShowSepayQr] = useState(false);
    const [transactionId, setTransactionId] = useState<string | null>(null);

    // Phone popup
    const [showPhonePopup, setShowPhonePopup] = useState(false);
    const [phoneInput, setPhoneInput] = useState('');
    const [phoneError, setPhoneError] = useState('');
    const [savingPhone, setSavingPhone] = useState(false);
    const [successMsg, setSuccessMsg] = useState('');

    useEffect(() => {
        loadPaymentHistory();
        loadBankInfo();
        // Get user phone from localStorage
        const userStr = localStorage.getItem('user');
        if (userStr) {
            const user = JSON.parse(userStr);
            setUserPhone(user.phone || '');
        }
    }, []);

    const loadPaymentHistory = async () => {
        try {
            setLoadingHistory(true);
            const response = await paymentApi.getList({ page: 1 });
            if (response.data.success) {
                const data = response.data.data.data || response.data.data;
                setPayments(Array.isArray(data) ? data.slice(0, 5) : []);
            }
        } catch (error) {
            console.error('Error loading payment history:', error);
        } finally {
            setLoadingHistory(false);
        }
    };

    const loadBankInfo = async () => {
        try {
            const response = await paymentApi.getBankInfo();
            if (response.data.success) {
                setBankInfo(response.data.data);
            }
        } catch (error) {
            console.error('Error loading bank info:', error);
            // Use default bank info
            setBankInfo({
                bank_name: 'BIDV',
                account_number: '8898144485',
                account_name: 'NGUYEN VAN PHUOC',
                branch: 'Chi nhánh Hồ Chí Minh',
            });
        }
    };

    const handleQuickAmount = (value: number) => {
        setAmount(value.toString());
        setError(null);
    };

    const handleCopy = (text: string, field: string) => {
        navigator.clipboard.writeText(text);
        setCopied(field);
        setTimeout(() => setCopied(null), 2000);
    };

    const validatePhone = (phone: string): boolean => {
        const phoneRegex = /^(0[3|5|7|8|9])[0-9]{8}$/;
        return phoneRegex.test(phone);
    };

    const handleSavePhone = async () => {
        const trimmed = phoneInput.trim();
        if (!trimmed) { setPhoneError('Vui lòng nhập số điện thoại'); return; }
        if (!validatePhone(trimmed)) { setPhoneError('Số điện thoại không hợp lệ (VD: 0901234567)'); return; }

        setSavingPhone(true);
        setPhoneError('');
        try {
            // Get current name from localStorage (backend requires name)
            const userStr = localStorage.getItem('user');
            const currentName = userStr ? JSON.parse(userStr).name || 'User' : 'User';
            const res = await userApi.updateProfile({ name: currentName, phone: trimmed });
            if (res.data?.success) {
                setUserPhone(trimmed);
                // Update localStorage
                const userStr = localStorage.getItem('user');
                if (userStr) {
                    const user = JSON.parse(userStr);
                    user.phone = trimmed;
                    localStorage.setItem('user', JSON.stringify(user));
                    window.dispatchEvent(new Event('userUpdated'));
                }
                setShowPhonePopup(false);
            } else {
                setPhoneError(res.data?.message || 'Lỗi cập nhật');
            }
        } catch (e: any) {
            setPhoneError(e.response?.data?.message || 'Lỗi kết nối');
        } finally {
            setSavingPhone(false);
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!userPhone) {
            setShowPhonePopup(true);
            return;
        }

        if (!amount || parseInt(amount) < 10000) {
            setError('Số tiền tối thiểu là 10,000đ');
            return;
        }

        setIsLoading(true);
        setError(null);

        try {
            let response;
            const amountValue = parseInt(amount);

            switch (selectedMethod) {
                case 'vnpay':
                    response = await paymentApi.createVnpay(amountValue);
                    if (response.data.success && response.data.data.payment_url) {
                        // Redirect to VNPay
                        window.location.href = response.data.data.payment_url;
                        return;
                    }
                    break;

                case 'sepay':
                    response = await paymentApi.createBankTransfer(amountValue);
                    if (response.data.success) {
                        setTransactionId(response.data.data.transaction_id);
                        setShowSepayQr(true);
                        loadPaymentHistory();
                    }
                    break;

                case 'bank':
                    response = await paymentApi.createBankTransfer(amountValue);
                    if (response.data.success) {
                        setSuccessMsg('Yêu cầu nạp tiền đã được tạo. Vui lòng chuyển khoản theo thông tin bên dưới.');
                        setTimeout(() => setSuccessMsg(''), 5000);
                        setTransactionId(response.data.data.transaction_id);
                        loadPaymentHistory();
                        setAmount('');
                    }
                    break;
            }

            if (response && !response.data.success) {
                setError(response.data.message || 'Có lỗi xảy ra');
            }
        } catch (error: any) {
            console.error('Payment error:', error);
            setError(error.response?.data?.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div>
            <h1 className={styles.pageTitle}>Nạp tiền</h1>
            <p className={styles.pageSubtitle}>Chọn phương thức thanh toán và nhập số tiền muốn nạp</p>

            <div className={styles.container}>
                <div className={styles.mainContent}>
                    {/* Payment Methods */}
                    <div className="card">
                        <h3 className={styles.sectionTitle}>Phương thức thanh toán</h3>
                        <div className={styles.methods}>
                            {paymentMethods.map((method) => (
                                <button
                                    key={method.id}
                                    type="button"
                                    className={`${styles.methodCard} ${selectedMethod === method.id ? styles.methodActive : ''}`}
                                    onClick={() => {
                                        if (!method.disabled) {
                                            setSelectedMethod(method.id);
                                            setError(null);
                                            setShowSepayQr(false);
                                        }
                                    }}
                                    style={method.disabled ? { opacity: 0.5, cursor: 'not-allowed' } : {}}
                                    disabled={method.disabled}
                                >
                                    <method.icon size={28} />
                                    <div>
                                        <h4>{method.name}</h4>
                                        <p>{method.description}</p>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Amount */}
                    <div className="card">
                        <h3 className={styles.sectionTitle}>Số tiền nạp</h3>

                        <div className={styles.quickAmounts}>
                            {quickAmounts.map((value) => (
                                <button
                                    key={value}
                                    type="button"
                                    className={`${styles.quickBtn} ${amount === value.toString() ? styles.quickBtnActive : ''}`}
                                    onClick={() => handleQuickAmount(value)}
                                >
                                    {value.toLocaleString('vi-VN', { maximumFractionDigits: 0 })}đ
                                </button>
                            ))}
                        </div>

                        <div className={styles.amountInput}>
                            <label className="label">Hoặc nhập số tiền</label>
                            <div className={styles.inputWrapper}>
                                <input
                                    type="number"
                                    className="input"
                                    placeholder="Nhập số tiền..."
                                    value={amount}
                                    onChange={(e) => {
                                        setAmount(e.target.value);
                                        setError(null);
                                        setShowSepayQr(false);
                                    }}
                                    min="10000"
                                    step="1000"
                                />
                                <span className={styles.currency}>VNĐ</span>
                            </div>
                            <p className={styles.hint}>Tối thiểu: 10,000đ - Tối đa: 100,000,000đ</p>
                        </div>

                        {error && (
                            <p className={styles.errorText}>{error}</p>
                        )}

                        {/* Success Notification */}
                        {successMsg && (
                            <div style={{
                                padding: '14px 16px', marginTop: '12px', borderRadius: '10px',
                                background: 'rgba(34, 197, 94, 0.12)', border: '1px solid rgba(34, 197, 94, 0.4)',
                                color: '#22c55e', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '14px'
                            }}>
                                <FiCheck size={20} />
                                <span>{successMsg}</span>
                                <button onClick={() => setSuccessMsg('')} style={{
                                    marginLeft: 'auto', background: 'none', border: 'none',
                                    color: '#22c55e', cursor: 'pointer', fontSize: '18px', padding: '0'
                                }}>×</button>
                            </div>
                        )}

                        {/* Phone Warning */}
                        {!userPhone && (
                            <button
                                type="button"
                                onClick={() => setShowPhonePopup(true)}
                                style={{
                                    width: '100%', padding: '12px 16px', marginTop: '12px',
                                    background: 'rgba(251, 191, 36, 0.1)', border: '1px solid rgba(251, 191, 36, 0.4)',
                                    borderRadius: '8px', color: '#fbbf24', cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', gap: '8px', fontSize: '14px'
                                }}
                            >
                                <FiPhone size={18} />
                                Bạn chưa có số điện thoại. Nhấn để cập nhật trước khi nạp tiền.
                            </button>
                        )}

                        {/* Bank Transfer Info */}
                        {selectedMethod === 'bank' && bankInfo && (
                            <div className={styles.bankInfo}>
                                <h4>Thông tin chuyển khoản thủ công</h4>
                                <div className={styles.bankDetails}>
                                    <div className={styles.bankRow}>
                                        <span>Ngân hàng:</span>
                                        <strong>{bankInfo.bank_name}</strong>
                                    </div>
                                    <div className={styles.bankRow}>
                                        <span>Số tài khoản:</span>
                                        <div className={styles.copyField}>
                                            <strong>{bankInfo.account_number}</strong>
                                            <button
                                                type="button"
                                                onClick={() => handleCopy(bankInfo.account_number, 'accountNumber')}
                                            >
                                                {copied === 'accountNumber' ? <FiCheck color="#22c55e" /> : <FiCopy />}
                                            </button>
                                        </div>
                                    </div>
                                    <div className={styles.bankRow}>
                                        <span>Chủ tài khoản:</span>
                                        <strong>{bankInfo.account_name}</strong>
                                    </div>
                                    <div className={styles.bankRow}>
                                        <span>Chi nhánh:</span>
                                        <strong>{bankInfo.branch}</strong>
                                    </div>
                                    <div className={styles.bankRow}>
                                        <span>Nội dung CK:</span>
                                        <div className={styles.copyField}>
                                            <strong className={styles.transferContent}>KHODAT {userPhone} {amount || '...'}</strong>
                                            <button
                                                type="button"
                                                onClick={() => handleCopy(`KHODAT ${userPhone} ${amount}`, 'content')}
                                            >
                                                {copied === 'content' ? <FiCheck color="#22c55e" /> : <FiCopy />}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p className={styles.bankNote}>
                                    * Sau khi chuyển khoản, vui lòng chờ Admin xác nhận thủ công (trong giờ hành chính).
                                </p>
                            </div>
                        )}

                        {/* Sepay QR Info */}
                        {selectedMethod === 'sepay' && bankInfo && showSepayQr && amount && (
                            <div className={styles.bankInfo} style={{ borderTop: '3px solid #22c55e' }}>
                                <h4 style={{ color: '#16a34a', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px' }}>
                                    <FiCheck /> Đã tạo mã QR tự động
                                </h4>
                                <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '16px 0' }}>
                                    <img
                                        src={`https://img.vietqr.io/image/${bankInfo.bank_name}-8898144485-compact2.png?amount=${amount}&addInfo=KHODAT%20${userPhone}%20${amount}&accountName=${encodeURIComponent(bankInfo.account_name)}`}
                                        alt="VietQR"
                                        style={{ width: '250px', height: '250px', borderRadius: '8px', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}
                                    />
                                    <p style={{ marginTop: '16px', fontSize: '14px', color: 'var(--text-light)', textAlign: 'center' }}>
                                        Quét mã QR bằng ứng dụng ngân hàng để thanh toán chính xác số tiền và nội dung. <br />
                                        <strong>Lưu ý: Không thay đổi nội dung chuyển khoản.</strong>
                                    </p>
                                </div>
                                <div className={styles.bankDetails}>
                                    <div className={styles.bankRow}>
                                        <span>Số tiền:</span>
                                        <strong style={{ color: '#ef4444', fontSize: '18px' }}>{parseInt(amount).toLocaleString('vi-VN')}đ</strong>
                                    </div>
                                    <div className={styles.bankRow}>
                                        <span>Nội dung CK:</span>
                                        <div className={styles.copyField}>
                                            <strong className={styles.transferContent}>KHODAT {userPhone} {amount}</strong>
                                            <button type="button" onClick={() => handleCopy(`KHODAT ${userPhone} ${amount}`, 'content')}>
                                                {copied === 'content' ? <FiCheck color="#22c55e" /> : <FiCopy />}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p className={styles.bankNote} style={{ color: '#16a34a', fontWeight: '500' }}>
                                    * Hệ thống sẽ tự động xác nhận và cộng tiền vào tài khoản trong 1-3 phút.
                                </p>
                            </div>
                        )}

                        {!showSepayQr && (
                            <button
                                type="submit"
                                className="btn btn-primary"
                                style={{ width: '100%', marginTop: '24px' }}
                                disabled={isLoading || !amount || parseInt(amount) < 10000}
                                onClick={handleSubmit}
                            >
                                {isLoading ? (
                                    <span className="spinner" />
                                ) : selectedMethod === 'bank' ? (
                                    'Xác nhận đã chuyển khoản'
                                ) : selectedMethod === 'sepay' ? (
                                    'Tạo mã QR Nạp Tiền'
                                ) : (
                                    `Nạp ${amount ? parseInt(amount).toLocaleString('vi-VN', { maximumFractionDigits: 0 }) : '0'}đ`
                                )}
                            </button>
                        )}
                    </div>
                </div>

                {/* Sidebar */}
                <div className={styles.sidebar}>
                    <div className="card">
                        <div className={styles.sectionHeader}>
                            <h3 className={styles.sectionTitle}>Lịch sử nạp tiền</h3>
                            <button
                                className={styles.refreshBtn}
                                onClick={loadPaymentHistory}
                                disabled={loadingHistory}
                            >
                                <FiRefreshCw className={loadingHistory ? styles.spinning : ''} />
                            </button>
                        </div>
                        <div className={styles.historyList}>
                            {loadingHistory ? (
                                <div className={styles.loadingSmall}>
                                    <div className={styles.spinnerSmall}></div>
                                </div>
                            ) : payments.length === 0 ? (
                                <p className={styles.emptyHistory}>Chưa có giao dịch nào</p>
                            ) : (
                                payments.map((payment) => {
                                    const status = getStatusBadge(payment.status);
                                    return (
                                        <div key={payment.id} className={styles.historyItem}>
                                            <div>
                                                <p className={styles.historyAmount}>
                                                    +{Math.floor(Number(payment.amount)).toLocaleString('vi-VN')}đ
                                                </p>
                                                <p className={styles.historyMethod}>
                                                    {getMethodLabel(payment.method)}
                                                </p>
                                            </div>
                                            <div className={styles.historyMeta}>
                                                <span className={`badge ${status.class}`}>{status.label}</span>
                                                <p className={styles.historyTime}>{formatDate(payment.created_at)}</p>
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Phone Number Popup */}
            {showPhonePopup && (
                <div style={{
                    position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.6)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 9999, padding: '16px'
                }} onClick={() => setShowPhonePopup(false)}>
                    <div style={{
                        background: 'var(--card-bg, #1e293b)', borderRadius: '16px', padding: '32px',
                        maxWidth: '420px', width: '100%', boxShadow: '0 25px 50px rgba(0,0,0,0.3)'
                    }} onClick={(e) => e.stopPropagation()}>
                        <div style={{ textAlign: 'center', marginBottom: '24px' }}>
                            <div style={{
                                width: '56px', height: '56px', borderRadius: '50%', margin: '0 auto 16px',
                                background: 'linear-gradient(135deg, #22c55e, #16a34a)',
                                display: 'flex', alignItems: 'center', justifyContent: 'center'
                            }}>
                                <FiPhone size={28} color="white" />
                            </div>
                            <h3 style={{ fontSize: '20px', fontWeight: 700, color: 'var(--text, #fff)', marginBottom: '8px' }}>
                                Cập nhật số điện thoại
                            </h3>
                            <p style={{ color: 'var(--text-light, #94a3b8)', fontSize: '14px' }}>
                                Vui lòng nhập số điện thoại để thực hiện nạp tiền
                            </p>
                        </div>
                        <div style={{ marginBottom: '16px' }}>
                            <input
                                type="tel"
                                value={phoneInput}
                                onChange={(e) => { setPhoneInput(e.target.value); setPhoneError(''); }}
                                placeholder="0901234567"
                                maxLength={10}
                                style={{
                                    width: '100%', padding: '14px 16px', borderRadius: '10px', fontSize: '16px',
                                    background: 'var(--input-bg, #0f172a)', color: 'var(--text, #fff)',
                                    border: phoneError ? '2px solid #ef4444' : '2px solid var(--border, #334155)',
                                    outline: 'none', textAlign: 'center', letterSpacing: '2px', fontWeight: 600,
                                    boxSizing: 'border-box'
                                }}
                                onKeyDown={(e) => e.key === 'Enter' && handleSavePhone()}
                            />
                            {phoneError && (
                                <p style={{ color: '#ef4444', fontSize: '13px', marginTop: '8px', textAlign: 'center' }}>{phoneError}</p>
                            )}
                        </div>
                        <div style={{ display: 'flex', gap: '12px' }}>
                            <button
                                onClick={() => setShowPhonePopup(false)}
                                style={{
                                    flex: 1, padding: '12px', borderRadius: '10px', border: '1px solid var(--border, #334155)',
                                    background: 'transparent', color: 'var(--text-light, #94a3b8)', cursor: 'pointer', fontSize: '15px'
                                }}
                            >Hủy</button>
                            <button
                                onClick={handleSavePhone}
                                disabled={savingPhone}
                                style={{
                                    flex: 1, padding: '12px', borderRadius: '10px', border: 'none',
                                    background: 'linear-gradient(135deg, #22c55e, #16a34a)', color: '#fff',
                                    cursor: savingPhone ? 'not-allowed' : 'pointer', fontSize: '15px', fontWeight: 600,
                                    opacity: savingPhone ? 0.7 : 1
                                }}
                            >{savingPhone ? 'Đang lưu...' : 'Lưu số điện thoại'}</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
