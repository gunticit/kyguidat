import apiClient from './client';
import type {
    Payment,
    PaymentListParams,
    CreatePaymentRequest,
    BankInfo,
    ApiResponse,
    PaginatedResponse
} from '@/types';

export const paymentService = {
    getList: (params?: PaymentListParams) =>
        apiClient.get<ApiResponse<PaginatedResponse<Payment>>>('/payments', { params }),

    getById: (id: number) =>
        apiClient.get<ApiResponse<Payment>>(`/payments/${id}`),

    createVnpay: (data: CreatePaymentRequest) =>
        apiClient.post<ApiResponse<{ payment_url: string }>>('/payments/vnpay/create', data),

    createMomo: (data: CreatePaymentRequest) =>
        apiClient.post<ApiResponse<{ payment_url: string }>>('/payments/momo/create', data),

    createBankTransfer: (data: CreatePaymentRequest) =>
        apiClient.post<ApiResponse<Payment & { bank_info: BankInfo }>>('/payments/bank-transfer/create', data),

    getBankInfo: () =>
        apiClient.get<ApiResponse<BankInfo>>('/payments/bank-info'),
};

export default paymentService;
