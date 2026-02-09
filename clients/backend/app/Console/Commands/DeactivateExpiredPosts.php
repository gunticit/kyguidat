<?php

namespace App\Console\Commands;

use App\Models\Consignment;
use Illuminate\Console\Command;

class DeactivateExpiredPosts extends Command
{
    protected $signature = 'posts:deactivate-expired';
    protected $description = 'Deactivate consignments that have been published for more than 30 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);

        $consignments = Consignment::whereNotNull('published_at')
            ->where('published_at', '<=', $cutoff)
            ->where('auto_deactivated', false)
            ->whereIn('status', [
                Consignment::STATUS_APPROVED,
                Consignment::STATUS_SELLING,
            ])
            ->get();

        $count = 0;
        foreach ($consignments as $consignment) {
            $consignment->update([
                'status' => Consignment::STATUS_DEACTIVATED,
                'auto_deactivated' => true,
                'deactivated_at' => now(),
            ]);
            $count++;
        }

        $this->info("Deactivated {$count} expired consignments.");

        return self::SUCCESS;
    }
}
