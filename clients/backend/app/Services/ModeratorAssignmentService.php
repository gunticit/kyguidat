<?php

namespace App\Services;

use App\Models\Consignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ModeratorAssignmentService
{
    /**
     * Get list of active moderators and auditors
     *
     * @return Collection<User>
     */
    public function getActiveModerators(): Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['moderator', 'auditor']);
        })
        ->where('status', 'active')
        ->get();
    }

    /**
     * Assign a consignment to the least-loaded active moderator
     */
    public function assignToLeastLoadedModerator(Consignment $consignment, bool $force = false): ?User
    {
        if (!$force && ($consignment->status !== Consignment::STATUS_PENDING || $consignment->assigned_to)) {
            return $consignment->assignee;
        }

        $moderators = $this->getActiveModerators();
        if ($moderators->isEmpty()) {
            return null;
        }

        // Exclude consignment creator if possible (so moderators don't review their own posts)
        $candidates = $moderators->filter(function ($m) use ($consignment) {
            return $m->id !== $consignment->user_id;
        });

        // Fallback to all moderators if no other candidate
        if ($candidates->isEmpty()) {
            $candidates = $moderators;
        }

        // Count current pending items assigned to each candidate
        $counts = Consignment::whereIn('assigned_to', $candidates->pluck('id'))
            ->where('status', Consignment::STATUS_PENDING)
            ->select('assigned_to', DB::raw('count(*) as total'))
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to')
            ->toArray();

        // Sort candidates by lowest pending count, then by ID (deterministic round-robin)
        $chosen = $candidates->sortBy(function ($m) use ($counts) {
            return $counts[$m->id] ?? 0;
        })->first();

        if ($chosen) {
            $consignment->assigned_to = $chosen->id;
            $consignment->assigned_at = now();
            $consignment->save();
        }

        return $chosen;
    }

    /**
     * Balance any pending consignments that do not have an assignee yet
     */
    public function balanceUnassignedPendingConsignments(): int
    {
        $unassigned = Consignment::where('status', Consignment::STATUS_PENDING)
            ->whereNull('assigned_to')
            ->orderBy('id', 'asc')
            ->get();

        $assignedCount = 0;
        foreach ($unassigned as $consignment) {
            $assigned = $this->assignToLeastLoadedModerator($consignment);
            if ($assigned) {
                $assignedCount++;
            }
        }

        return $assignedCount;
    }

    /**
     * Get statistics for a specific moderator filtered by month and year
     */
    public function getModeratorStats(User $moderator, ?int $month = null, ?int $year = null): array
    {
        $year = $year ?: (int) date('Y');

        $baseDateQuery = function ($query, string $dateColumn) use ($month, $year) {
            $query->whereYear($dateColumn, $year);
            if ($month && $month >= 1 && $month <= 12) {
                $query->whereMonth($dateColumn, $month);
            }
        };

        // Total assigned in period
        $assignedQuery = Consignment::where('assigned_to', $moderator->id);
        $baseDateQuery($assignedQuery, 'assigned_at');
        $totalAssigned = $assignedQuery->count();

        // Total approved by this moderator in period
        $approvedQuery = Consignment::where('approved_by', $moderator->id)->where('status', Consignment::STATUS_APPROVED);
        $baseDateQuery($approvedQuery, 'approved_at');
        $totalApproved = $approvedQuery->count();

        // Total rejected by this moderator in period
        // Check both direct status=rejected or consignment history
        $rejectedQuery = Consignment::where('approved_by', $moderator->id)->where('status', Consignment::STATUS_REJECTED);
        $baseDateQuery($rejectedQuery, 'updated_at');
        $totalRejected = $rejectedQuery->count();

        // Currently pending (backlog)
        $currentPending = Consignment::where('assigned_to', $moderator->id)
            ->where('status', Consignment::STATUS_PENDING)
            ->count();

        // Total own consignments posted
        $ownConsignments = Consignment::where('user_id', $moderator->id)->count();

        $totalProcessed = $totalApproved + $totalRejected;
        $completionRate = ($totalAssigned > 0)
            ? round(($totalProcessed / $totalAssigned) * 100, 1)
            : ($totalProcessed > 0 ? 100.0 : 0.0);

        // Chart data
        $chartData = [];
        if ($month && $month >= 1 && $month <= 12) {
            // Group by days in this month
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $chartData[$dayStr] = [
                    'label' => sprintf('%02d/%02d', $d, $month),
                    'approved' => 0,
                    'rejected' => 0,
                    'assigned' => 0,
                ];
            }

            // Fill approved
            $dailyApproved = Consignment::where('approved_by', $moderator->id)
                ->where('status', Consignment::STATUS_APPROVED)
                ->whereYear('approved_at', $year)
                ->whereMonth('approved_at', $month)
                ->select(DB::raw('DATE(approved_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            foreach ($dailyApproved as $date => $cnt) {
                if (isset($chartData[$date])) {
                    $chartData[$date]['approved'] = (int) $cnt;
                }
            }

            // Fill rejected
            $dailyRejected = Consignment::where('approved_by', $moderator->id)
                ->where('status', Consignment::STATUS_REJECTED)
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->select(DB::raw('DATE(updated_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            foreach ($dailyRejected as $date => $cnt) {
                if (isset($chartData[$date])) {
                    $chartData[$date]['rejected'] = (int) $cnt;
                }
            }
        } else {
            // Group by 12 months in this year
            for ($m = 1; $m <= 12; $m++) {
                $monthKey = sprintf('%04d-%02d', $year, $m);
                $chartData[$monthKey] = [
                    'label' => 'T' . $m,
                    'approved' => 0,
                    'rejected' => 0,
                    'assigned' => 0,
                ];
            }

            $monthlyApproved = Consignment::where('approved_by', $moderator->id)
                ->where('status', Consignment::STATUS_APPROVED)
                ->whereYear('approved_at', $year)
                ->select(DB::raw('DATE_FORMAT(approved_at, "%Y-%m") as m'), DB::raw('count(*) as count'))
                ->groupBy('m')
                ->pluck('count', 'm')
                ->toArray();

            foreach ($monthlyApproved as $mKey => $cnt) {
                if (isset($chartData[$mKey])) {
                    $chartData[$mKey]['approved'] = (int) $cnt;
                }
            }

            $monthlyRejected = Consignment::where('approved_by', $moderator->id)
                ->where('status', Consignment::STATUS_REJECTED)
                ->whereYear('updated_at', $year)
                ->select(DB::raw('DATE_FORMAT(updated_at, "%Y-%m") as m'), DB::raw('count(*) as count'))
                ->groupBy('m')
                ->pluck('count', 'm')
                ->toArray();

            foreach ($monthlyRejected as $mKey => $cnt) {
                if (isset($chartData[$mKey])) {
                    $chartData[$mKey]['rejected'] = (int) $cnt;
                }
            }
        }

        // Recent moderated items
        $recentActions = Consignment::where('approved_by', $moderator->id)
            ->whereIn('status', [Consignment::STATUS_APPROVED, Consignment::STATUS_REJECTED])
            ->select(['id', 'code', 'title', 'price', 'status', 'approved_at', 'updated_at', 'reject_reason'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'moderator' => [
                'id' => $moderator->id,
                'name' => $moderator->name,
                'email' => $moderator->email,
            ],
            'period' => [
                'month' => $month,
                'year' => $year,
            ],
            'stats' => [
                'total_assigned' => $totalAssigned,
                'total_approved' => $totalApproved,
                'total_rejected' => $totalRejected,
                'total_processed' => $totalProcessed,
                'current_pending' => $currentPending,
                'own_consignments' => $ownConsignments,
                'completion_rate' => $completionRate,
            ],
            'chart_data' => array_values($chartData),
            'recent_actions' => $recentActions,
        ];
    }

    /**
     * Get leaderboard and comparative statistics for all moderators
     */
    public function getLeaderboard(?int $month = null, ?int $year = null, ?int $filterModeratorId = null): array
    {
        $year = $year ?: (int) date('Y');
        $moderators = $this->getActiveModerators();

        if ($filterModeratorId) {
            $moderators = $moderators->where('id', $filterModeratorId);
        }

        $leaderboard = [];

        foreach ($moderators as $mod) {
            $stats = $this->getModeratorStats($mod, $month, $year)['stats'];

            $leaderboard[] = [
                'id' => $mod->id,
                'name' => $mod->name,
                'email' => $mod->email,
                'phone' => $mod->phone,
                'status' => $mod->status,
                'total_assigned' => $stats['total_assigned'],
                'total_approved' => $stats['total_approved'],
                'total_rejected' => $stats['total_rejected'],
                'total_processed' => $stats['total_processed'],
                'current_pending' => $stats['current_pending'],
                'completion_rate' => $stats['completion_rate'],
                // Weighted score: 10 pts per approval, 5 pts per review, penalize unresolved backlog
                'score' => ($stats['total_approved'] * 10) + ($stats['total_rejected'] * 5) - ($stats['current_pending'] * 2),
            ];
        }

        // Sort by total_processed desc, then total_approved desc
        usort($leaderboard, function ($a, $b) {
            if ($b['total_processed'] === $a['total_processed']) {
                return $b['total_approved'] <=> $a['total_approved'];
            }
            return $b['total_processed'] <=> $a['total_processed'];
        });

        // Assign ranks (1, 2, 3...)
        foreach ($leaderboard as $index => &$item) {
            $item['rank'] = $index + 1;
        }

        // Overall totals across moderators
        $systemTotals = [
            'total_moderators' => count($leaderboard),
            'total_approved' => array_sum(array_column($leaderboard, 'total_approved')),
            'total_rejected' => array_sum(array_column($leaderboard, 'total_rejected')),
            'total_processed' => array_sum(array_column($leaderboard, 'total_processed')),
            'total_pending' => array_sum(array_column($leaderboard, 'current_pending')),
        ];

        return [
            'period' => [
                'month' => $month,
                'year' => $year,
            ],
            'system_totals' => $systemTotals,
            'leaderboard' => $leaderboard,
        ];
    }
}
