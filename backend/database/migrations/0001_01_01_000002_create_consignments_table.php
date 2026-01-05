<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('quantity')->default(1);
            $table->json('images')->nullable();
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'selling',
                'sold',
                'cancelled'
            ])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('code');
        });

        Schema::create('consignment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_id')->constrained()->onDelete('cascade');
            $table->string('status', 50);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            $table->index('consignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_histories');
        Schema::dropIfExists('consignments');
    }
};
