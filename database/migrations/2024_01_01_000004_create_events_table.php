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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('venue_id');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->timestampTz('start_at');
            $table->timestampTz('end_at')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->timestampTz('updated_at')->default(DB::raw('now()'));
            
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('restrict');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('start_at');
            $table->index('venue_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

