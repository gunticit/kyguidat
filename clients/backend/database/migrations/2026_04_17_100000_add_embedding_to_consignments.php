<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->string('embedding_hash', 64)->nullable()->after('keywords');
            $table->timestamp('embedded_at')->nullable()->after('embedding_hash');

            $table->index('embedding_hash');
            $table->index('embedded_at');
        });
    }

    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropIndex(['embedding_hash']);
            $table->dropIndex(['embedded_at']);
            $table->dropColumn(['embedding_hash', 'embedded_at']);
        });
    }
};
