import axios from 'axios';

const API_URL = process.env.NEXT_PUBLIC_API_URL || '/api';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add auth token to requests
api.interceptors.request.use((config) => {
    if (typeof window !== 'undefined') {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
    }
    return config;
});

// Handle auth errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            if (typeof window !== 'undefined') {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

// Auth APIs
export const authApi = {
    login: (data: { email: string; password: string }) =>
        api.post('/auth/login', data),
    register: (data: { name: string; email: string; password: string; password_confirmation: string }) =>
        api.post('/auth/register', data),
    logout: () =>
        api.post('/auth/logout'),
    me: () =>
        api.get('/auth/me'),
    forgotPassword: (email: string) =>
        api.post('/auth/forgot-password', { email }),
    resetPassword: (data: { email: string; token: string; password: string; password_confirmation: string }) =>
        api.post('/auth/reset-password', data),
    deleteAccount: (data: { password?: string; confirm?: string }) =>
        api.delete('/auth/account', { data }),
};

// User APIs
export const userApi = {
    getProfile: () =>
        api.get('/user/profile'),
    updateProfile: (data: { name?: string; phone?: string; avatar?: string }) =>
        api.put('/user/profile', data),
    updatePassword: (data: { current_password: string; new_password: string; new_password_confirmation: string }) =>
        api.put('/user/password', data),
};

// Dashboard APIs
export const dashboardApi = {
    getOverview: () =>
        api.get('/dashboard'),
    getStats: () =>
        api.get('/dashboard/stats'),
    getRecentActivities: (limit = 10) =>
        api.get(`/dashboard/recent-activities?limit=${limit}`),
};

// Consignment APIs
export interface ConsignmentCreateData {
    title: string;
    description?: string;
    address: string;
    google_map_link?: string;
    price: number;
    min_price?: number;
    seller_phone: string;
    images?: string[];
    description_files?: string[];
    note_to_admin?: string;
}

export interface ConsignmentUpdateData {
    title?: string;
    description?: string;
    address?: string;
    google_map_link?: string;
    price?: number;
    min_price?: number;
    seller_phone?: string;
    images?: string[];
    description_files?: string[];
    note_to_admin?: string;
}

export const consignmentApi = {
    getList: (params?: { status?: string; search?: string; page?: number }) =>
        api.get('/consignments', { params }),
    getById: (id: number) =>
        api.get(`/consignments/${id}`),
    create: (data: ConsignmentCreateData) =>
        api.post('/consignments', data),
    update: (id: number, data: ConsignmentUpdateData) =>
        api.put(`/consignments/${id}`, data),
    delete: (id: number) =>
        api.delete(`/consignments/${id}`),
    cancel: (id: number) =>
        api.post(`/consignments/${id}/cancel`),
    getHistory: (id: number) =>
        api.get(`/consignments/${id}/history`),
    reactivate: (id: number) =>
        api.post(`/consignments/${id}/reactivate`),
    getPostingQuota: () =>
        api.get('/posting-quota'),
};

// Payment APIs
export const paymentApi = {
    getList: (params?: { status?: string; method?: string; page?: number }) =>
        api.get('/payments', { params }),
    getById: (id: number) =>
        api.get(`/payments/${id}`),
    createVnpay: (amount: number) =>
        api.post('/payments/vnpay/create', { amount }),
    createMomo: (amount: number) =>
        api.post('/payments/momo/create', { amount }),
    createBankTransfer: (amount: number) =>
        api.post('/payments/bank-transfer/create', { amount }),
    getBankInfo: () =>
        api.get('/payments/bank-info'),
};

// Support APIs
export const supportApi = {
    getList: (params?: { status?: string; category?: string; page?: number }) =>
        api.get('/supports', { params }),
    getById: (id: number) =>
        api.get(`/supports/${id}`),
    create: (data: { subject: string; category?: string; priority?: string; message: string; attachments?: string[] }) =>
        api.post('/supports', data),
    update: (id: number, data: Partial<{ subject: string; category: string; priority: string }>) =>
        api.put(`/supports/${id}`, data),
    delete: (id: number) =>
        api.delete(`/supports/${id}`),
    addMessage: (id: number, data: { message: string; attachments?: string[] }) =>
        api.post(`/supports/${id}/messages`, data),
    getMessages: (id: number) =>
        api.get(`/supports/${id}/messages`),
    close: (id: number) =>
        api.post(`/supports/${id}/close`),
};

// Posting Package APIs
export const postingPackageApi = {
    getList: () =>
        api.get('/posting-packages'),
    getById: (id: number) =>
        api.get(`/posting-packages/${id}`),
    purchase: (packageId: number) =>
        api.post('/posting-packages/purchase', { package_id: packageId }),
    getMyPackages: () =>
        api.get('/my-packages'),
    getCurrentPackage: () =>
        api.get('/my-packages/current'),
};

// Upload APIs
export const uploadApi = {
    uploadMultiple: (files: File[], directory = 'consignments') => {
        const formData = new FormData();
        files.forEach(file => formData.append('images[]', file));
        formData.append('directory', directory);
        return api.post('/upload/images-optimized', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    uploadSingle: (file: File, directory = 'uploads') => {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('directory', directory);
        return api.post('/upload/image-optimized', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
};

export default api;
