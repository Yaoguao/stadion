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
        Schema::table('seat_instances', function (Blueprint $table) {
            $table->foreign('reserved_by_booking_id')
                ->references('id')
                ->on('bookings')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_instances', function (Blueprint $table) {
            $table->dropForeign(['reserved_by_booking_id']);
        });
    }
};

