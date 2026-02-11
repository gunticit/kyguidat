// API Configuration
export const API_URL = process.env.NEXT_PUBLIC_API_URL || '/api';

// Social Login URLs
export const SOCIAL_LOGIN_URLS = {
    google: process.env.NEXT_PUBLIC_GOOGLE_LOGIN_URL || `${API_URL}/auth/google`,
    facebook: process.env.NEXT_PUBLIC_FACEBOOK_LOGIN_URL || `${API_URL}/auth/facebook`,
    zalo: process.env.NEXT_PUBLIC_ZALO_LOGIN_URL || `${API_URL}/auth/zalo`,
};

// App Configuration
export const APP_CONFIG = {
    name: 'Ký Gửi Đất Vuôn',
    description: 'Nền tảng ký gửi trực tuyến',
    maxFileSize: 5 * 1024 * 1024, // 5MB
    allowedImageTypes: ['image/jpeg', 'image/png', 'image/webp'],
    paginationLimit: 10,
};

// Local Storage Keys
export const STORAGE_KEYS = {
    authToken: 'auth_token',
    user: 'user',
    theme: 'theme',
};

// Route Paths
export const ROUTES = {
    home: '/',
    login: '/login',
    register: '/register',
    forgotPassword: '/forgot-password',
    dashboard: '/dashboard',
    consignments: '/dashboard/consignments',
    newConsignment: '/dashboard/consignments/new',
    packages: '/dashboard/packages',
    deposit: '/dashboard/deposit',
    support: '/dashboard/support',
    profile: '/dashboard/profile',
};
