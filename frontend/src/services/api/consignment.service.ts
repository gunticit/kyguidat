import apiClient from './client';
import type {
    Consignment,
    ConsignmentHistory,
    CreateConsignmentRequest,
    UpdateConsignmentRequest,
    ConsignmentListParams,
    ApiResponse,
    PaginatedResponse
} from '@/types';

export const consignmentService = {
    getList: (params?: ConsignmentListParams) =>
        apiClient.get<ApiResponse<PaginatedResponse<Consignment>>>('/consignments', { params }),

    getById: (id: number) =>
        apiClient.get<ApiResponse<Consignment>>(`/consignments/${id}`),

    create: (data: CreateConsignmentRequest) =>
        apiClient.post<ApiResponse<Consignment>>('/consignments', data),

    update: (id: number, data: UpdateConsignmentRequest) =>
        apiClient.put<ApiResponse<Consignment>>(`/consignments/${id}`, data),

    delete: (id: number) =>
        apiClient.delete<ApiResponse<null>>(`/consignments/${id}`),

    cancel: (id: number) =>
        apiClient.post<ApiResponse<Consignment>>(`/consignments/${id}/cancel`),

    getHistory: (id: number) =>
        apiClient.get<ApiResponse<ConsignmentHistory[]>>(`/consignments/${id}/history`),
};

export default consignmentService;
