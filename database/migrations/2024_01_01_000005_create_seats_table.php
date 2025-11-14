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
        Schema::create('seats', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('venue_id');
            $table->string('sector', 20);
            $table->string('zone', 50)->nullable();
            $table->integer('row_num');
            $table->integer('seat_number');
            $table->decimal('base_price', 10, 2);
            $table->smallInteger('view_rating')->nullable();
            $table->boolean('is_wheelchair')->default(false);
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');
            $table->unique(['venue_id', 'sector', 'row_num', 'seat_number']);
            
            $table->check('base_price >= 0');
            $table->check('view_rating IS NULL OR (view_rating >= 1 AND view_rating <= 5)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};

