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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');

            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone')->nullable();



            $table->integer('number_of_guests');

            $table->decimal('total_price', 10, 2);

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed'])->default('unpaid');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
