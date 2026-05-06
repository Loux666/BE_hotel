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
        Schema::table('room_holds', function (Blueprint $table) {
            // Rename expired_at to expires_at
            $table->renameColumn('expired_at', 'expires_at');
            
            // Add hold_token
            $table->string('hold_token')->unique()->after('checkout');
            
            // Ensure user_id is not nullable as per plan
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            // Remove unneeded columns from legacy
            $table->dropColumn(['session_id', 'quantity']);
            
            // Add indexes for performance
            $table->index(['room_id', 'checkin', 'checkout', 'expires_at'], 'room_holds_availability_index');
            $table->index(['user_id', 'expires_at'], 'room_holds_user_expires_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_holds', function (Blueprint $table) {
            $table->dropIndex('room_holds_availability_index');
            $table->dropIndex('room_holds_user_expires_index');
            
            $table->string('session_id')->nullable()->after('user_id');
            $table->integer('quantity')->default(1)->after('checkout');
            
            $table->dropColumn('hold_token');
            
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            $table->renameColumn('expires_at', 'expired_at');
        });
    }
};
