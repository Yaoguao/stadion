<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('booking_id');
            $table->string('provider', 50)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->string('transaction_id', 200)->unique()->nullable();
            $table->jsonb('provider_data')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->string('idempotency_key', 255)->nullable();
            
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->index('booking_id', 'idx_payments_booking');
            $table->index('status', 'idx_payments_status');
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
