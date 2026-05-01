<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('deactivated_at');
            $table->index('expires_at');
            $table->index(['status', 'expires_at'], 'idx_status_expires');
        });

        DB::table('consignments')
            ->whereIn('status', ['approved', 'selling'])
            ->whereNotNull('published_at')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $r) {
                    $publishedExpire = strtotime($r->published_at) + 30 * 86400;
                    $bufferExpire = time() + 7 * 86400;
                    $expiresAt = max($publishedExpire, $bufferExpire);
                    DB::table('consignments')
                        ->where('id', $r->id)
                        ->update(['expires_at' => date('Y-m-d H:i:s', $expiresAt)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropIndex('idx_status_expires');
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });
    }
};
