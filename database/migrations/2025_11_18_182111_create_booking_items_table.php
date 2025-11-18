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
        Schema::create('booking_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('booking_id');
            $table->uuid('seat_instance_id');
            $table->decimal('price', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('seat_instance_id')->references('id')->on('seat_instances')->onDelete('restrict');
            $table->unique(['booking_id', 'seat_instance_id']);
            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
