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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');

            $table->string('room_name');            // Lưu snapshot tên phòng tại thời điểm đặt
            $table->decimal('price_per_night', 10, 2); // Giá phòng/đêm tại thời điểm đặt
            $table->integer('nights');
            $table->date('checkin')->nullable();
            $table->date('checkout')->nullable();              // Số đêm lưu trú
            $table->integer('quantity')->default(1); // Số lượng phòng cùng loại
            $table->decimal('subtotal', 10, 2);     // Tổng tiền cho chi tiết này = price_per_night * nights * quantity

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};
