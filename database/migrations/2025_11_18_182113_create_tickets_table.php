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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('booking_item_id');
            $table->text('qr_code')->unique();
            $table->timestampTz('issued_at')->default(DB::raw('now()'));
            $table->boolean('validated')->default(false);
            $table->timestampTz('validated_at')->nullable();
            $table->string('seat_label', 100)->nullable();
            
            $table->foreign('booking_item_id')->references('id')->on('booking_items')->onDelete('cascade');
            $table->index('validated', 'idx_tickets_validated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
