<?php

namespace App\Services;

use App\Models\User;
use App\Models\Consignment;
use App\Models\Payment;
use App\Models\SupportTicket;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get dashboard overview data
     */
    public function getOverview(User $user): array
    {
        $wallet = $user->wallet;

        return [
            'wallet' => [
                'balance' => $wallet?->balance ?? 0,
                'frozen_balance' => $wallet?->frozen_balance ?? 0,
            ],
            'consignments' => [
                'total' => $user->consignments()->count(),
                'pending' => $user->consignments()->where('status', 'pending')->count(),
                'selling' => $user->consignments()->where('status', 'selling')->count(),
                'sold' => $user->consignments()->where('status', 'sold')->count(),
            ],
            'payments' => [
                'total_deposited' => $user->payments()
                    ->where('status', 'completed')
                    ->sum('net_amount'),
                'pending' => $user->payments()
                    ->where('status', 'pending')
                    ->count(),
            ],
            'support' => [
                'open_tickets' => $user->supportTickets()
                    ->whereIn('status', ['open', 'in_progress', 'waiting_reply'])
                    ->count(),
            ],
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getStats(User $user): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        return [
            'monthly_deposit' => $user->payments()
                ->where('status', 'completed')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('net_amount'),
            'monthly_consignments' => $user->consignments()
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->count(),
            'chart_data' => $this->getChartData($user),
        ];
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(User $user, int $limit = 10): array
    {
        $activities = collect();

        // Recent consignments
        $consignments = $user->consignments()
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn($c) => [
                'type' => 'consignment',
                'title' => 'Ký gửi: ' . $c->title,
                'status' => $c->status,
                'created_at' => $c->created_at,
            ]);

        // Recent payments
        $payments = $user->payments()
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn($p) => [
                'type' => 'payment',
                'title' => 'Nạp tiền: ' . number_format($p->amount) . 'đ',
                'status' => $p->status,
                'created_at' => $p->created_at,
            ]);

        // Recent support tickets
        $tickets = $user->supportTickets()
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn($t) => [
                'type' => 'support',
                'title' => 'Hỗ trợ: ' . $t->subject,
                'status' => $t->status,
                'created_at' => $t->created_at,
            ]);

        return $activities
            ->merge($consignments)
            ->merge($payments)
            ->merge($tickets)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Get chart data for dashboard
     */
    private function getChartData(User $user): array
    {
        $data = [];
        $now = Carbon::now();

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $data[] = [
                'date' => $date->format('d/m'),
                'deposits' => $user->payments()
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('net_amount'),
                'consignments' => $user->consignments()
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        return $data;
    }
}
