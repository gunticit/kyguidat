import axios, { AxiosInstance, AxiosError } from 'axios';
import { API_URL } from '@/constants/config';

/**
 * Create configured axios instance
 */
const createApiClient = (): AxiosInstance => {
    const client = axios.create({
        baseURL: API_URL,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        timeout: 30000, // 30 seconds
    });

    // Request interceptor - Add auth token
    client.interceptors.request.use(
        (config) => {
            if (typeof window !== 'undefined') {
                const token = localStorage.getItem('auth_token');
                if (token) {
                    config.headers.Authorization = `Bearer ${token}`;
                }
            }
            return config;
        },
        (error) => Promise.reject(error)
    );

    // Response interceptor - Handle errors
    client.interceptors.response.use(
        (response) => response,
        (error: AxiosError<{ message?: string; success?: boolean }>) => {
            const status = error.response?.status;
            const message = error.response?.data?.message;

            if (typeof window !== 'undefined') {
                switch (status) {
                    case 401:
                        // Unauthorized — clear session, redirect to login
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user');
                        window.location.href = '/login';
                        break;

                    case 403:
                        // Forbidden — email not verified or insufficient permissions
                        if (message?.includes('Email chưa được xác thực')) {
                            // Redirect to a verification prompt page
                            window.location.href = '/dashboard?verify=required';
                        }
                        console.error('[403 Forbidden]', message);
                        break;

                    case 422:
                        // Validation errors — handled by calling code
                        console.warn('[422 Validation]', error.response?.data);
                        break;

                    case 429:
                        // Rate limited
                        console.warn('[429 Too Many Requests] Vui lòng thử lại sau');
                        break;

                    default:
                        if (status && status >= 500) {
                            console.error('[Server Error]', status, message);
                        }
                        break;
                }
            }

            return Promise.reject(error);
        }
    );

    return client;
};

export const apiClient = createApiClient();
export default apiClient;

