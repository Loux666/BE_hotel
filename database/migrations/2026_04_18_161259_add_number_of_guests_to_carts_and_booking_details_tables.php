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
        Schema::table('carts', function (Blueprint $table) {
            $table->integer('number_of_guests')->default(1)->after('checkout');
        });

        Schema::table('booking_details', function (Blueprint $table) {
            $table->integer('number_of_guests')->default(1)->after('checkout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('number_of_guests');
        });

        Schema::table('booking_details', function (Blueprint $table) {
            $table->dropColumn('number_of_guests');
        });
    }
};
