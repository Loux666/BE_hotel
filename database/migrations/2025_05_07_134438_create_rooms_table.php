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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name');
            $table->string('room_image')->nullable();
            $table->float('price');
            $table->text('description')->nullable();
            $table->string('wifi')->default('Yes');
            $table->string('capacity');
            $table->string('type');
            $table->string('status')->nullable();
            $table->timestamps();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
