<?php

namespace App\Console\Commands;

use App\Models\Consignment;
use App\Models\ConsignmentHistory;
use Illuminate\Console\Command;

class AutoDeactivateConsignments extends Command
{
    protected $signature = 'consignments:auto-deactivate {--days=30 : Number of days before auto-deactivation}';
    protected $description = 'Auto-deactivate consignments that have been published for more than N days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $consignments = Consignment::whereIn('status', [
            Consignment::STATUS_APPROVED,
            Consignment::STATUS_SELLING,
        ])
            ->where(function ($q) use ($cutoff) {
                // Use published_at if set, otherwise approved_at, otherwise created_at
                $q->where(function ($q2) use ($cutoff) {
                    $q2->whereNotNull('published_at')->where('published_at', '<', $cutoff);
                })->orWhere(function ($q2) use ($cutoff) {
                    $q2->whereNull('published_at')->whereNotNull('approved_at')->where('approved_at', '<', $cutoff);
                })->orWhere(function ($q2) use ($cutoff) {
                    $q2->whereNull('published_at')->whereNull('approved_at')->where('created_at', '<', $cutoff);
                });
            })
            ->get();

        if ($consignments->isEmpty()) {
            $this->info("No consignments to deactivate.");
            return 0;
        }

        $count = 0;
        foreach ($consignments as $consignment) {
            $oldStatus = $consignment->status;

            $consignment->update([
                'status' => Consignment::STATUS_DEACTIVATED,
                'auto_deactivated' => true,
                'deactivated_at' => now(),
            ]);

            ConsignmentHistory::create([
                'consignment_id' => $consignment->id,
                'status' => Consignment::STATUS_DEACTIVATED,
                'note' => "Tự động tắt sau {$days} ngày (từ {$oldStatus})",
                'changed_by' => $consignment->user_id,
            ]);

            $count++;
        }

        $this->info("Deactivated {$count} consignments.");
        return 0;
    }
}
