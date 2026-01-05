// Payment types
export interface Payment {
    id: number;
    user_id: number;
    transaction_id: string;
    method: PaymentMethod;
    amount: number;
    fee: number;
    net_amount: number;
    status: PaymentStatus;
    gateway_transaction_id?: string;
    gateway_response?: Record<string, unknown>;
    paid_at?: string;
    expired_at?: string;
    created_at: string;
    updated_at: string;
}

export type PaymentMethod = 'vnpay' | 'momo' | 'bank_transfer';
export type PaymentStatus = 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled' | 'expired';

export interface PaymentListParams {
    status?: string;
    method?: string;
    page?: number;
    per_page?: number;
}

export interface CreatePaymentRequest {
    amount: number;
}

export interface BankInfo {
    bank_name: string;
    account_number: string;
    account_name: string;
    branch?: string;
    content_template: string;
}
