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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Mã giảm giá
            $table->enum('type', ['fixed', 'percent']); // Giảm tiền hoặc phần trăm
            $table->decimal('value', 10, 2); // VD: 100000 hoặc 10 (%)
            $table->integer('max_uses')->nullable(); // VD: chỉ dùng được 100 lần
            $table->integer('user_limit')->nullable();
            $table->integer('used_count')->default(0); // Đã dùng bao nhiêu lần
            $table->decimal('min_order_price', 10, 2)->nullable(); // Áp dụng từ bao nhiêu tiền
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
