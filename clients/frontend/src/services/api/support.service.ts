import apiClient from './client';
import type {
    SupportTicket,
    SupportMessage,
    CreateSupportRequest,
    SupportListParams,
    ApiResponse,
    PaginatedResponse
} from '@/types';

export const supportService = {
    getList: (params?: SupportListParams) =>
        apiClient.get<ApiResponse<PaginatedResponse<SupportTicket>>>('/supports', { params }),

    getById: (id: number) =>
        apiClient.get<ApiResponse<SupportTicket>>(`/supports/${id}`),

    create: (data: CreateSupportRequest) =>
        apiClient.post<ApiResponse<SupportTicket>>('/supports', data),

    update: (id: number, data: Partial<CreateSupportRequest>) =>
        apiClient.put<ApiResponse<SupportTicket>>(`/supports/${id}`, data),

    delete: (id: number) =>
        apiClient.delete<ApiResponse<null>>(`/supports/${id}`),

    addMessage: (id: number, data: { message: string; attachments?: string[] }) =>
        apiClient.post<ApiResponse<SupportMessage>>(`/supports/${id}/messages`, data),

    getMessages: (id: number) =>
        apiClient.get<ApiResponse<SupportMessage[]>>(`/supports/${id}/messages`),

    close: (id: number) =>
        apiClient.post<ApiResponse<SupportTicket>>(`/supports/${id}/close`),
};

export default supportService;
