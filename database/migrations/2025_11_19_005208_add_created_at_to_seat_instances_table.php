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
        Schema::table('seat_instances', function (Blueprint $table) {
            $table->timestampTz('created_at')->default(DB::raw('now()'))->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_instances', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};
