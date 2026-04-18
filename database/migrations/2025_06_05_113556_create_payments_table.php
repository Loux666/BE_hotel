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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('txn_ref')->unique(); // vnp_TxnRef
            $table->string('transaction_no')->nullable(); // vnp_TransactionNo
            $table->string('bank_code')->nullable();
            $table->string('card_type')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_gateway')->default('vnpay');
            $table->string('status')->default('success'); // success | failed | pending
            $table->timestamp('paid_at')->nullable(); // vnp_PayDate
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
