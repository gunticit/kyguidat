<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('consignments') && !Schema::hasColumn('consignments', 'approved_by')) {
            Schema::table('consignments', function (Blueprint $table) {
                $table->unsignedBigInteger('approved_by')->nullable();
                if (Schema::hasTable('users')) {
                    $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('consignments') && Schema::hasColumn('consignments', 'approved_by')) {
            Schema::table('consignments', function (Blueprint $table) {
                try {
                    $table->dropForeign(['approved_by']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key does not exist
                }
                $table->dropColumn('approved_by');
            });
        }
    }
};
