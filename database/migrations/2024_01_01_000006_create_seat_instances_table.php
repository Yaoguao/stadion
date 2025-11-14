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
        Schema::create('seat_instances', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('event_id');
            $table->uuid('seat_id');
            $table->decimal('price', 10, 2);
            $table->string('status', 20)->default('available');
            $table->uuid('reserved_by_booking_id')->nullable();
            $table->timestampTz('reserved_expires_at')->nullable();
            $table->timestampTz('updated_at')->default(DB::raw('now()'));
            
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('seat_id')->references('id')->on('seats')->onDelete('restrict');
            $table->unique(['event_id', 'seat_id']);
            
            $table->check('price >= 0');
        });

        Schema::table('seat_instances', function (Blueprint $table) {
            $table->index('event_id', 'idx_seat_instances_event');
            $table->index('status', 'idx_seat_instances_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_instances');
    }
};

