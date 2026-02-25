<?php

namespace App\Console\Commands;

use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateBase64Images extends Command
{
    protected $signature = 'images:migrate-base64 
                            {--dry-run : Show what would be migrated without making changes}
                            {--limit=0 : Limit number of records to process (0 = all)}
                            {--table=consignments : Table to migrate}';

    protected $description = 'Migrate base64 images to MinIO/S3 as WebP files';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $table = $this->option('table');

        $this->info($dryRun ? '🔍 DRY RUN MODE' : '🚀 MIGRATING IMAGES');
        $this->line("Table: {$table}");

        $optimizer = new ImageOptimizer();

        // Query records with base64 data
        $query = DB::table($table)
            ->where(function ($q) {
                $q->where('featured_image', 'LIKE', 'data:image%')
                    ->orWhere('images', 'LIKE', '%data:image%');
            });

        $total = $query->count();
        $this->info("Found {$total} records with base64 images");

        if ($total === 0) {
            $this->info('✅ Nothing to migrate');
            return 0;
        }

        $records = $limit > 0 ? $query->limit($limit)->get() : $query->get();
        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        $migrated = 0;
        $errors = 0;

        foreach ($records as $record) {
            try {
                $updates = [];

                // Migrate featured_image
                if (!empty($record->featured_image) && str_starts_with($record->featured_image, 'data:image')) {
                    if ($dryRun) {
                        $this->line("\n  [DRY] #{$record->id} featured_image: base64 (" . strlen($record->featured_image) . " bytes)");
                    } else {
                        $result = $optimizer->optimizeBase64AndUpload(
                            $record->featured_image,
                            'consignments/featured',
                            ['thumbnail' => true]
                        );
                        $updates['featured_image'] = $result['url'];
                    }
                }

                // Migrate images array
                if (!empty($record->images)) {
                    $images = is_string($record->images) ? json_decode($record->images, true) : $record->images;

                    if (is_array($images)) {
                        $newImages = [];
                        $hasBase64 = false;

                        foreach ($images as $img) {
                            if (is_string($img) && str_starts_with($img, 'data:image')) {
                                $hasBase64 = true;
                                if ($dryRun) {
                                    $this->line("\n  [DRY] #{$record->id} images[]: base64 (" . strlen($img) . " bytes)");
                                    $newImages[] = $img;
                                } else {
                                    $result = $optimizer->optimizeBase64AndUpload($img, 'consignments/gallery');
                                    $newImages[] = $result['url'];
                                }
                            } else {
                                $newImages[] = $img;
                            }
                        }

                        if ($hasBase64 && !$dryRun) {
                            $updates['images'] = json_encode($newImages);
                        }
                    }
                }

                // Apply updates
                if (!$dryRun && !empty($updates)) {
                    DB::table($table)->where('id', $record->id)->update($updates);
                    $migrated++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("\n  Error #{$record->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("🔍 Dry run complete. {$total} records would be processed.");
        } else {
            $this->info("✅ Migration complete: {$migrated} migrated, {$errors} errors");
        }

        return $errors > 0 ? 1 : 0;
    }
}
