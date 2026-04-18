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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('request')->nullable()->after('guest_phone');
        });
    }


    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('request');
        });
    }
};
