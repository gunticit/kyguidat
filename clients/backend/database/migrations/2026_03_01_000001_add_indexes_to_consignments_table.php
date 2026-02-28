<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->index('status');
            $table->index('province');
            $table->index('consigner_name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['province']);
            $table->dropIndex(['consigner_name']);
            $table->dropIndex(['created_at']);
        });
    }
};
