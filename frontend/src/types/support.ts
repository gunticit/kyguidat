// Support Ticket types
export interface SupportTicket {
    id: number;
    user_id: number;
    ticket_number: string;
    subject: string;
    category: SupportCategory;
    priority: SupportPriority;
    status: SupportStatus;
    assigned_to?: number;
    closed_at?: string;
    created_at: string;
    updated_at: string;
    messages?: SupportMessage[];
}

export type SupportCategory = 'general' | 'payment' | 'consignment' | 'account' | 'other';
export type SupportPriority = 'low' | 'medium' | 'high' | 'urgent';
export type SupportStatus = 'open' | 'in_progress' | 'waiting_reply' | 'resolved' | 'closed';

export interface SupportMessage {
    id: number;
    support_ticket_id: number;
    user_id: number;
    message: string;
    attachments?: string[];
    is_admin: boolean;
    created_at: string;
    user?: {
        id: number;
        name: string;
        avatar?: string;
    };
}

export interface CreateSupportRequest {
    subject: string;
    category?: SupportCategory;
    priority?: SupportPriority;
    message: string;
    attachments?: string[];
}

export interface SupportListParams {
    status?: string;
    category?: string;
    page?: number;
    per_page?: number;
}
