/**
 * Validate email format
 */
export const isValidEmail = (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

/**
 * Validate Vietnamese phone number
 */
export const isValidPhone = (phone: string): boolean => {
    const phoneRegex = /^(0|84)(3|5|7|8|9)[0-9]{8}$/;
    return phoneRegex.test(phone.replace(/\s|-/g, ''));
};

/**
 * Validate password strength
 */
export const validatePassword = (password: string): {
    isValid: boolean;
    errors: string[];
} => {
    const errors: string[] = [];

    if (password.length < 6) {
        errors.push('Mật khẩu phải có ít nhất 6 ký tự');
    }
    if (password.length > 50) {
        errors.push('Mật khẩu không được quá 50 ký tự');
    }

    return {
        isValid: errors.length === 0,
        errors,
    };
};

/**
 * Validate required field
 */
export const isRequired = (value: string | null | undefined): boolean => {
    return value !== null && value !== undefined && value.trim().length > 0;
};

/**
 * Validate min length
 */
export const minLength = (value: string, min: number): boolean => {
    return value.length >= min;
};

/**
 * Validate max length
 */
export const maxLength = (value: string, max: number): boolean => {
    return value.length <= max;
};

/**
 * Validate number range
 */
export const inRange = (value: number, min: number, max: number): boolean => {
    return value >= min && value <= max;
};

/**
 * Validate positive number
 */
export const isPositive = (value: number): boolean => {
    return value > 0;
};
