<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('cancelled_at');
            $table->boolean('auto_deactivated')->default(false)->after('published_at');
            $table->timestamp('deactivated_at')->nullable()->after('auto_deactivated');
        });
    }

    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'auto_deactivated', 'deactivated_at']);
        });
    }
};
