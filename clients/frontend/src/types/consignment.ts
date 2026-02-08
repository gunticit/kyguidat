// Consignment types
export interface Consignment {
    id: number;
    user_id: number;
    code: string;
    title: string;
    description?: string;
    category?: string;
    price: number;
    quantity: number;
    images: string[];
    status: ConsignmentStatus;
    admin_note?: string;
    approved_at?: string;
    sold_at?: string;
    cancelled_at?: string;
    created_at: string;
    updated_at: string;
}

export type ConsignmentStatus = 'pending' | 'approved' | 'rejected' | 'selling' | 'sold' | 'cancelled';

export interface ConsignmentHistory {
    id: number;
    consignment_id: number;
    status: string;
    note?: string;
    changed_by: number;
    created_at: string;
}

export interface CreateConsignmentRequest {
    title: string;
    description?: string;
    category?: string;
    price: number;
    quantity?: number;
    images?: string[];
}

export interface UpdateConsignmentRequest {
    title?: string;
    description?: string;
    category?: string;
    price?: number;
    quantity?: number;
    images?: string[];
}

export interface ConsignmentListParams {
    status?: string;
    search?: string;
    page?: number;
    per_page?: number;
}
