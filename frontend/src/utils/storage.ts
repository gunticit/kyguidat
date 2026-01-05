import { STORAGE_KEYS } from '@/constants';

/**
 * Get item from localStorage
 */
export const getStorageItem = <T>(key: string, defaultValue: T | null = null): T | null => {
    if (typeof window === 'undefined') return defaultValue;

    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch {
        return defaultValue;
    }
};

/**
 * Set item to localStorage
 */
export const setStorageItem = <T>(key: string, value: T): void => {
    if (typeof window === 'undefined') return;

    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
        console.error('Error saving to localStorage:', error);
    }
};

/**
 * Remove item from localStorage
 */
export const removeStorageItem = (key: string): void => {
    if (typeof window === 'undefined') return;
    localStorage.removeItem(key);
};

/**
 * Clear all app storage
 */
export const clearAppStorage = (): void => {
    if (typeof window === 'undefined') return;

    Object.values(STORAGE_KEYS).forEach((key) => {
        localStorage.removeItem(key);
    });
};

/**
 * Get auth token
 */
export const getAuthToken = (): string | null => {
    if (typeof window === 'undefined') return null;
    return localStorage.getItem(STORAGE_KEYS.authToken);
};

/**
 * Check if user is authenticated
 */
export const isAuthenticated = (): boolean => {
    return !!getAuthToken();
};
