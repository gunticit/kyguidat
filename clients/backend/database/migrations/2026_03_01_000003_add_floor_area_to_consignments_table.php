<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->decimal('floor_area', 10, 2)->nullable()->after('area_dimensions');
        });
    }

    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropColumn('floor_area');
        });
    }
};
