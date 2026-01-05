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
        // Bảng gói đăng bài
        Schema::create('posting_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên gói: Gói 1 tháng, Gói 2 tháng, ...
            $table->string('slug')->unique(); // Slug: 1-month, 2-months, ...
            $table->text('description')->nullable(); // Mô tả gói
            $table->integer('duration_months'); // Thời gian: 1, 2, 3, 6 tháng
            $table->decimal('price', 12, 0); // Giá gói (VND)
            $table->decimal('original_price', 12, 0)->nullable(); // Giá gốc (nếu có giảm giá)
            $table->integer('post_limit')->default(-1); // Số bài đăng tối đa (-1 = không giới hạn)
            $table->integer('featured_posts')->default(0); // Số bài nổi bật được phép
            $table->boolean('priority_support')->default(false); // Hỗ trợ ưu tiên
            $table->json('features')->nullable(); // Các tính năng bổ sung
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->boolean('is_popular')->default(false); // Đánh dấu gói phổ biến
            $table->integer('sort_order')->default(0); // Thứ tự sắp xếp
            $table->timestamps();
        });

        // Bảng gói đăng bài của người dùng
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('posting_package_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 12, 0); // Số tiền đã trả
            $table->timestamp('started_at'); // Ngày bắt đầu
            $table->timestamp('expires_at'); // Ngày hết hạn
            $table->integer('posts_used')->default(0); // Số bài đã sử dụng
            $table->integer('featured_posts_used')->default(0); // Số bài nổi bật đã sử dụng
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_method')->nullable(); // vnpay, momo, wallet, bank_transfer
            $table->string('transaction_id')->nullable(); // Mã giao dịch
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_packages');
        Schema::dropIfExists('posting_packages');
    }
};
