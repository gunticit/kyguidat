/**
 * Format number as Vietnamese currency
 */
export const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
};

/**
 * Format number with thousand separators
 */
export const formatNumber = (num: number): string => {
    return new Intl.NumberFormat('vi-VN').format(num);
};

/**
 * Parse currency string to number
 */
export const parseCurrency = (value: string): number => {
    return parseInt(value.replace(/[^\d]/g, ''), 10) || 0;
};

/**
 * Format percentage
 */
export const formatPercent = (value: number, decimals: number = 0): string => {
    return `${value.toFixed(decimals)}%`;
};
