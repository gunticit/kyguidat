/**
 * Format date to Vietnamese format
 */
export const formatDate = (date: string | Date, includeTime: boolean = false): string => {
    const d = new Date(date);
    const options: Intl.DateTimeFormatOptions = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    };

    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }

    return d.toLocaleDateString('vi-VN', options);
};

/**
 * Format date to relative time (e.g., "2 phút trước")
 */
export const formatTimeAgo = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    const diffWeeks = Math.floor(diffDays / 7);
    const diffMonths = Math.floor(diffDays / 30);

    if (diffSecs < 60) return 'Vừa xong';
    if (diffMins < 60) return `${diffMins} phút trước`;
    if (diffHours < 24) return `${diffHours} giờ trước`;
    if (diffDays < 7) return `${diffDays} ngày trước`;
    if (diffWeeks < 4) return `${diffWeeks} tuần trước`;
    if (diffMonths < 12) return `${diffMonths} tháng trước`;

    return formatDate(dateString);
};

/**
 * Format date range
 */
export const formatDateRange = (start: string | Date, end: string | Date): string => {
    return `${formatDate(start)} - ${formatDate(end)}`;
};

/**
 * Check if date is today
 */
export const isToday = (date: string | Date): boolean => {
    const d = new Date(date);
    const today = new Date();
    return d.toDateString() === today.toDateString();
};

/**
 * Check if date is in the past
 */
export const isPast = (date: string | Date): boolean => {
    return new Date(date) < new Date();
};

/**
 * Get remaining days until date
 */
export const getRemainingDays = (date: string | Date): number => {
    const target = new Date(date);
    const now = new Date();
    const diffMs = target.getTime() - now.getTime();
    return Math.max(0, Math.ceil(diffMs / (1000 * 60 * 60 * 24)));
};
