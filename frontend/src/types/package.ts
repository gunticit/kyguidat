// Posting Package types
export interface PostingPackage {
    id: number;
    name: string;
    slug: string;
    description?: string;
    duration_months: number;
    price: number;
    original_price?: number;
    formatted_price: string;
    formatted_original_price?: string;
    discount_percentage: number;
    post_limit: number; // -1 = unlimited
    featured_posts: number;
    priority_support: boolean;
    features?: string[];
    is_active: boolean;
    is_popular: boolean;
    sort_order: number;
}

export interface UserPackage {
    id: number;
    user_id: number;
    posting_package_id: number;
    package_name: string;
    duration_months: number;
    amount_paid: number;
    started_at: string;
    expires_at: string;
    remaining_days: number;
    posts_used: number;
    remaining_posts: number | string;
    status: 'active' | 'expired' | 'cancelled';
    payment_status: 'pending' | 'paid' | 'failed';
    is_active: boolean;
    payment_method?: string;
}

export interface CurrentPackage {
    id: number;
    package_name: string;
    duration_months: number;
    started_at: string;
    expires_at: string;
    remaining_days: number;
    remaining_posts: number | string;
    can_create_post: boolean;
}

export interface PurchasePackageRequest {
    package_id: number;
}
