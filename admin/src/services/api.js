import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8080/api',
    headers: {
        'Content-Type': 'application/json'
    }
})

// Request interceptor for auth token
api.interceptors.request.use((config) => {
    // Get token directly from localStorage to avoid Pinia timing issues
    const token = localStorage.getItem('admin_token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

// Response interceptor for error handling
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Clear token and redirect to login
            localStorage.removeItem('admin_token')
            localStorage.removeItem('admin_user')
            if (window.location.pathname !== '/login') {
                window.location.href = '/login'
            }
        }
        return Promise.reject(error)
    }
)

export default api

// Auth functions
export const login = (email, password) => api.post('/auth/login', { email, password })

// API functions
export const adminApi = {
    // Dashboard
    getDashboard: () => api.get('/admin/dashboard'),

    // Users - CRUD
    getUsers: (params) => api.get('/admin/users', { params }),
    getUser: (id) => api.get(`/admin/users/${id}`),
    createUser: (data) => api.post('/admin/users', data),
    updateUser: (id, data) => api.put(`/admin/users/${id}`, data),
    deleteUser: (id) => api.delete(`/admin/users/${id}`),

    // Customers (frontend-registered users)
    getCustomers: (params) => api.get('/admin/customers', { params }),

    // Roles (User Groups) - CRUD
    getRoles: (params) => api.get('/admin/roles', { params }),
    getRole: (id) => api.get(`/admin/roles/${id}`),
    createRole: (data) => api.post('/admin/roles', data),
    updateRole: (id, data) => api.put(`/admin/roles/${id}`, data),
    deleteRole: (id) => api.delete(`/admin/roles/${id}`),

    // Consignments - CRUD
    getConsignments: (params) => api.get('/admin/consignments', { params }),
    getConsignment: (id) => api.get(`/admin/consignments/${id}`),
    createConsignment: (data) => api.post('/admin/consignments', data),
    updateConsignment: (id, data) => api.put(`/admin/consignments/${id}`, data),
    deleteConsignment: (id) => api.delete(`/admin/consignments/${id}`),
    approveConsignment: (id) => api.put(`/admin/consignments/${id}/approve`),
    rejectConsignment: (id, reason) => api.put(`/admin/consignments/${id}/reject`, { reason }),

    // Transactions
    getTransactions: (params) => api.get('/admin/transactions', { params }),

    // Support Tickets
    getSupportTickets: (params) => api.get('/admin/supports', { params }),
    getSupportTicket: (id) => api.get(`/admin/supports/${id}`),
    replySupportTicket: (id, data) => api.post(`/admin/supports/${id}/reply`, data),
    updateTicketStatus: (id, data) => api.put(`/admin/supports/${id}/status`, data),
    closeSupportTicket: (id) => api.post(`/admin/supports/${id}/close`),

    // Reports
    getReportOverview: () => api.get('/admin/reports/overview'),
    exportReport: () => api.get('/admin/reports/export', { responseType: 'blob' }),

    // Articles
    getArticles: (params) => api.get('/admin/articles', { params }),
    getArticle: (id) => api.get(`/admin/articles/${id}`),
    createArticle: (data) => api.post('/admin/articles', data),
    updateArticle: (id, data) => api.put(`/admin/articles/${id}`, data),
    deleteArticle: (id) => api.delete(`/admin/articles/${id}`),
    checkSlug: (params) => api.get('/admin/check-slug', { params }),

    // Pages
    getPages: (params) => api.get('/admin/pages', { params }),
    getPage: (id) => api.get(`/admin/pages/${id}`),
    createPage: (data) => api.post('/admin/pages', data),
    updatePage: (id, data) => api.put(`/admin/pages/${id}`, data),
    deletePage: (id) => api.delete(`/admin/pages/${id}`),

    // Administrative Divisions — Provinces
    getProvinces: (params) => api.get('/admin/provinces', { params }),
    createProvince: (data) => api.post('/admin/provinces', data),
    updateProvince: (id, data) => api.put(`/admin/provinces/${id}`, data),
    deleteProvince: (id) => api.delete(`/admin/provinces/${id}`),

    // Administrative Divisions — Wards
    getWards: (params) => api.get('/admin/wards', { params }),
    createWard: (data) => api.post('/admin/wards', data),
    updateWard: (id, data) => api.put(`/admin/wards/${id}`, data),
    deleteWard: (id) => api.delete(`/admin/wards/${id}`),

    // Upload - Optimized Image (WebP conversion)
    uploadOptimizedImage: (file, directory = 'consignments') => {
        const formData = new FormData()
        formData.append('image', file)
        formData.append('directory', directory)
        return api.post('/upload/image-optimized', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    },

    uploadMultipleOptimizedImages: (files, directory = 'consignments') => {
        const formData = new FormData()
        files.forEach(file => formData.append('images[]', file))
        formData.append('directory', directory)
        return api.post('/upload/images-optimized', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    },
}

