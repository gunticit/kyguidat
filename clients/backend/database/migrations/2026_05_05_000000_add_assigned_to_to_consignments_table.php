<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('consignments') && !Schema::hasColumn('consignments', 'assigned_to')) {
            Schema::table('consignments', function (Blueprint $table) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('approved_by');
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');

                if (Schema::hasTable('users')) {
                    $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                }

                $table->index(['assigned_to', 'status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('consignments') && Schema::hasColumn('consignments', 'assigned_to')) {
            Schema::table('consignments', function (Blueprint $table) {
                try {
                    $table->dropForeign(['assigned_to']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key was not created
                }
                $table->dropIndex(['assigned_to', 'status']);
                $table->dropColumn(['assigned_to', 'assigned_at']);
            });
        }
    }
};
