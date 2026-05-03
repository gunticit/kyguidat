<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_packages', function (Blueprint $table) {
            // Per-UserPackage post quota that can grow when stacking purchases.
            // -1 = unlimited (matches post_limit semantic on posting_packages).
            $table->integer('total_posts_allowed')->default(0)->after('featured_posts_used');
        });

        // Backfill: snapshot post_limit from the parent posting_package.
        DB::statement("
            UPDATE user_packages up
            JOIN posting_packages pp ON up.posting_package_id = pp.id
            SET up.total_posts_allowed = pp.post_limit
            WHERE up.total_posts_allowed = 0
        ");
    }

    public function down(): void
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropColumn('total_posts_allowed');
        });
    }
};
