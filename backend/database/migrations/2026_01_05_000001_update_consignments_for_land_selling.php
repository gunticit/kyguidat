<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update consignments table for land selling requirements
     */
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            // Add new columns for land selling
            $table->string('address')->nullable()->after('description');
            $table->string('google_map_link', 500)->nullable()->after('address');
            $table->decimal('min_price', 15, 2)->nullable()->after('price');
            $table->string('seller_phone', 20)->nullable()->after('min_price');
            $table->json('description_files')->nullable()->after('images');
            $table->text('note_to_admin')->nullable()->after('description_files');
            
            // Remove unused columns
            $table->dropColumn(['category', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            // Restore removed columns
            $table->string('category', 100)->nullable();
            $table->integer('quantity')->default(1);
            
            // Remove new columns
            $table->dropColumn([
                'address',
                'google_map_link',
                'min_price',
                'seller_phone',
                'description_files',
                'note_to_admin'
            ]);
        });
    }
};
