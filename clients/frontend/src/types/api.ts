// Common API Response types
export interface ApiResponse<T = unknown> {
    success: boolean;
    message?: string;
    data: T;
    errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface PaginatedApiResponse<T> extends ApiResponse<PaginatedResponse<T>> { }

// Common params
export interface PaginationParams {
    page?: number;
    per_page?: number;
}

// Error types
export interface ApiError {
    success: false;
    message: string;
    errors?: Record<string, string[]>;
}
