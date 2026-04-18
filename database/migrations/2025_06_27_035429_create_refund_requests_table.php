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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');

            $table->enum('type', ['vnpay', 'offline'])->default('vnpay'); // Loại hoàn tiền
            $table->enum('status', ['pending', 'approved', 'rejected', 'done'])->default('pending');

            $table->integer('amount'); // Tổng tiền hoàn lại (sau giảm giá nếu có)
            $table->string('reason')->nullable(); // Lý do user nhập
            $table->text('admin_note')->nullable(); // Admin nhập khi duyệt (optional)

            $table->unsignedBigInteger('approved_by')->nullable(); // admin_id hoặc manager_id
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamp('approved_at')->nullable(); // Thời gian admin xử lý
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
