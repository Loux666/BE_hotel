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
        Schema::table('room_availabilities', function (Blueprint $table) {
            $table->unsignedInteger('available_rooms')->default(0)->after('price_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_availabilities', function (Blueprint $table) {
            //
        });
    }
};
