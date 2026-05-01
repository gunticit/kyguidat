<?php

namespace App\Console\Commands;

use App\Models\Consignment;
use App\Models\ConsignmentHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoDeactivateConsignments extends Command
{
    protected $signature = 'consignments:auto-deactivate
                            {--dry-run : Show candidates without writing changes}
                            {--limit=1000 : Max records per run}
                            {--fallback-days=30 : Fallback expiration days for rows missing expires_at}';

    protected $description = 'Auto-deactivate consignments whose expires_at has passed';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $fallbackDays = (int) $this->option('fallback-days');

        // Backfill safety net: rows in approved/selling without expires_at fall back to
        // published_at + fallbackDays. Old rows from before this feature won't be missed.
        $fallbackCutoff = now()->subDays($fallbackDays);

        $baseQuery = fn () => Consignment::query()
            ->whereIn('status', [Consignment::STATUS_APPROVED, Consignment::STATUS_SELLING])
            ->where(function ($q) use ($fallbackCutoff) {
                $q->where('expires_at', '<=', now())
                  ->orWhere(function ($q2) use ($fallbackCutoff) {
                      $q2->whereNull('expires_at')
                         ->whereNotNull('published_at')
                         ->where('published_at', '<', $fallbackCutoff);
                  });
            });

        $count = $baseQuery()->count();
        $this->info("Found {$count} consignments to deactivate (cap={$limit})");

        if ($count === 0) {
            return self::SUCCESS;
        }

        if ($dryRun) {
            $baseQuery()->limit($limit)->select('id', 'code', 'status', 'expires_at', 'published_at')->get()
                ->each(fn ($c) => $this->line("  [{$c->id}] {$c->code} expires={$c->expires_at} published={$c->published_at}"));
            return self::SUCCESS;
        }

        $deactivated = 0;
        $now = now();
        // chunkById ignores outer limit(); track count manually and stop early.
        $baseQuery()->chunkById(100, function ($consignments) use (&$deactivated, $now, $limit) {
            DB::transaction(function () use ($consignments, &$deactivated, $now, $limit) {
                foreach ($consignments as $c) {
                    if ($deactivated >= $limit) {
                        return false;
                    }
                    $oldStatus = $c->status;
                    $c->update([
                        'status' => Consignment::STATUS_DEACTIVATED,
                        'auto_deactivated' => true,
                        'deactivated_at' => $now,
                    ]);
                    ConsignmentHistory::create([
                        'consignment_id' => $c->id,
                        'status' => Consignment::STATUS_DEACTIVATED,
                        'note' => "Tự động tắt: hết hạn (trước đó {$oldStatus}, expires_at=" . ($c->expires_at ?? 'null') . ')',
                        'changed_by' => null,
                    ]);
                    $deactivated++;
                }
            });
            return $deactivated < $limit;
        });

        Log::info("Auto-deactivated {$deactivated} expired consignments");
        $this->info("Deactivated {$deactivated} consignments");

        return self::SUCCESS;
    }
}
