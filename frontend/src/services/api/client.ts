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
        (error: AxiosError) => {
            if (error.response?.status === 401) {
                if (typeof window !== 'undefined') {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                }
            }
            return Promise.reject(error);
        }
    );

    return client;
};

export const apiClient = createApiClient();
export default apiClient;
