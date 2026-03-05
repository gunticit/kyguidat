<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->text('google_map_link')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->string('google_map_link', 500)->nullable()->change();
        });
    }
};
