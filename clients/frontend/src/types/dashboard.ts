// Dashboard types
export interface DashboardOverview {
    wallet: {
        balance: number;
        frozen_balance: number;
    };
    consignments: {
        total: number;
        pending: number;
        selling: number;
        sold: number;
    };
    payments: {
        total_deposited: number;
        pending: number;
    };
    support: {
        open_tickets: number;
    };
}

export interface DashboardStats {
    monthly_deposit: number;
    monthly_consignments: number;
    chart_data: ChartDataPoint[];
}

export interface ChartDataPoint {
    date: string;
    deposits: number;
    consignments: number;
}

export interface ActivityItem {
    type: 'consignment' | 'payment' | 'support';
    title: string;
    status: string;
    created_at: string;
}
