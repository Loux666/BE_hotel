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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_name');
            $table->string('hotel_image')->nullable();
            $table->string('hotel_city');
            $table->string('hotel_address');
            $table->text('hotel_description')->nullable();
            $table->float('hotel_rating')->nullable();
            $table->string('hotel_phone');
            $table->string('hotel_email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
