'use client';

import { useState, useEffect } from 'react';
import { FiCreditCard, FiSmartphone, FiDollarSign, FiCopy, FiCheck, FiRefreshCw } from 'react-icons/fi';
import { paymentApi } from '@/lib/api';
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
    { id: 'vnpay', name: 'VNPay', icon: FiCreditCard, description: 'Thanh toán qua ATM/Internet Banking', disabled: false },
    { id: 'bank', name: 'Chuyển khoản', icon: FiDollarSign, description: 'Chuyển khoản ngân hàng trực tiếp', disabled: false },
    { id: 'momo', name: 'Momo', icon: FiSmartphone, description: 'Ví điện tử Momo (Sắp ra mắt)', disabled: true },
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
        bank_transfer: 'Chuyển khoản',
    };
    return methodMap[method] || method;
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

export default function DepositPage() {
    const [selectedMethod, setSelectedMethod] = useState('vnpay');
    const [amount, setAmount] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [copied, setCopied] = useState<string | null>(null);
    const [payments, setPayments] = useState<Payment[]>([]);
    const [bankInfo, setBankInfo] = useState<BankInfo | null>(null);
    const [loadingHistory, setLoadingHistory] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [userPhone, setUserPhone] = useState('');

    useEffect(() => {
        loadPaymentHistory();
        loadBankInfo();
        // Get user phone from localStorage
        const userStr = localStorage.getItem('user');
        if (userStr) {
            const user = JSON.parse(userStr);
            setUserPhone(user.phone || '0901234567');
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
                bank_name: 'Vietcombank',
                account_number: '1234567890123',
                account_name: 'CONG TY TNHH KHODAT',
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

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
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

                case 'momo':
                    response = await paymentApi.createMomo(amountValue);
                    if (response.data.success && response.data.data.payment_url) {
                        // Redirect to Momo
                        window.location.href = response.data.data.payment_url;
                        return;
                    }
                    break;

                case 'bank':
                    response = await paymentApi.createBankTransfer(amountValue);
                    if (response.data.success) {
                        alert('Yêu cầu nạp tiền đã được tạo. Vui lòng chuyển khoản theo thông tin bên dưới.');
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
                                    {value.toLocaleString('vi-VN')}đ
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

                        {/* Bank Transfer Info */}
                        {selectedMethod === 'bank' && bankInfo && (
                            <div className={styles.bankInfo}>
                                <h4>Thông tin chuyển khoản</h4>
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
                                            <strong className={styles.transferContent}>KHODAT {userPhone}</strong>
                                            <button
                                                type="button"
                                                onClick={() => handleCopy(`KHODAT ${userPhone}`, 'content')}
                                            >
                                                {copied === 'content' ? <FiCheck color="#22c55e" /> : <FiCopy />}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p className={styles.bankNote}>
                                    * Sau khi chuyển khoản, số dư sẽ được cộng trong vòng 5-15 phút (giờ hành chính)
                                </p>
                            </div>
                        )}

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
                            ) : (
                                `Nạp ${amount ? parseInt(amount).toLocaleString('vi-VN') : '0'}đ`
                            )}
                        </button>
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
                                                    +{payment.amount.toLocaleString('vi-VN')}đ
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
        </div>
    );
}
