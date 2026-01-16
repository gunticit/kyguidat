<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ipn_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Tên cấu hình (VNPay IPN, Momo IPN, etc.)
            $table->string('provider', 50); // vnpay, momo, bank, custom
            $table->string('ipn_url', 500); // URL nhận thông báo
            $table->string('secret_key', 255)->nullable(); // Secret key để verify
            $table->string('merchant_id', 100)->nullable(); // Mã merchant
            $table->json('additional_config')->nullable(); // Cấu hình bổ sung
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('trigger_count')->default(0);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['provider', 'is_active']);
        });

        // Bảng log các IPN requests
        Schema::create('ipn_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipn_configuration_id')->nullable()->constrained()->onDelete('set null');
            $table->string('provider', 50);
            $table->string('transaction_id', 100)->nullable();
            $table->string('order_id', 100)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('status', 50)->nullable(); // pending, success, failed
            $table->string('response_code', 20)->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['provider', 'transaction_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipn_logs');
        Schema::dropIfExists('ipn_configurations');
    }
};
