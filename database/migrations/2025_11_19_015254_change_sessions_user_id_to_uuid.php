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
        // Удаляем внешний ключ, если он существует
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Изменяем тип колонки на UUID
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE uuid USING user_id::uuid');

        // Восстанавливаем внешний ключ
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем внешний ключ
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Возвращаем тип колонки на bigint (но это может не сработать, если есть UUID значения)
        // В реальности лучше не делать rollback для этого изменения
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE bigint USING NULL');
    }
};
