<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add new fields for land consignment management
     */
    public function up(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            // Category
            $table->unsignedBigInteger('category_id')->nullable()->after('user_id');

            // Order and notification
            $table->integer('order_number')->nullable()->after('code');
            $table->date('notification_date')->nullable()->after('order_number');

            // Featured image
            $table->text('featured_image')->nullable()->after('images');

            // Notes
            $table->text('notes')->nullable()->after('note_to_admin');
            $table->text('internal_note')->nullable()->after('notes');

            // Land type
            $table->string('type', 100)->nullable()->after('internal_note');

            // Land classification (JSON arrays)
            $table->json('land_directions')->nullable()->after('type');
            $table->json('land_types')->nullable()->after('land_directions');

            // Location details
            $table->string('road_display', 50)->nullable()->after('land_types');
            $table->string('province', 100)->nullable()->after('road_display');
            $table->string('ward', 100)->nullable()->after('province');
            $table->decimal('frontage_actual', 10, 2)->nullable()->after('ward');
            $table->string('frontage_range', 50)->nullable()->after('frontage_actual');
            $table->string('area_range', 50)->nullable()->after('frontage_range');
            $table->string('has_house', 20)->nullable()->after('area_range');
            $table->decimal('residential_area', 10, 2)->nullable()->after('has_house');
            $table->string('road', 255)->nullable()->after('residential_area');
            $table->string('area_dimensions', 100)->nullable()->after('road');
            $table->string('latitude', 50)->nullable()->after('area_dimensions');
            $table->string('longitude', 50)->nullable()->after('latitude');

            // Consigner information
            $table->string('consigner_name', 255)->nullable()->after('longitude');
            $table->string('consigner_phone', 50)->nullable()->after('consigner_name');
            $table->string('consigner_type', 50)->nullable()->after('consigner_phone');

            // Land registry
            $table->string('sheet_number', 50)->nullable()->after('consigner_type');
            $table->string('parcel_number', 50)->nullable()->after('sheet_number');

            // SEO
            $table->text('keywords')->nullable()->after('parcel_number');
            $table->string('seo_url', 500)->nullable()->after('keywords');

            // Display order
            $table->integer('display_order')->default(1)->after('seo_url');

            // Reject reason
            $table->text('reject_reason')->nullable()->after('admin_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropColumn([
                'category_id',
                'order_number',
                'notification_date',
                'featured_image',
                'notes',
                'internal_note',
                'type',
                'land_directions',
                'land_types',
                'road_display',
                'province',
                'ward',
                'frontage_actual',
                'frontage_range',
                'area_range',
                'has_house',
                'residential_area',
                'road',
                'area_dimensions',
                'latitude',
                'longitude',
                'consigner_name',
                'consigner_phone',
                'consigner_type',
                'sheet_number',
                'parcel_number',
                'keywords',
                'seo_url',
                'display_order',
                'reject_reason'
            ]);
        });
    }
};
