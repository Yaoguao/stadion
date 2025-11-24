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
        // Проверяем и удаляем существующий внешний ключ, если он есть
        $foreignKeys = DB::select("
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = 'sessions' 
            AND constraint_type = 'FOREIGN KEY'
            AND constraint_name LIKE '%user_id%'
        ");
        
        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE sessions DROP CONSTRAINT IF EXISTS {$foreignKey->constraint_name}");
        }
        
        // Очищаем данные user_id, так как они несовместимы с UUID (bigint нельзя преобразовать в UUID)
        DB::table('sessions')->whereNotNull('user_id')->update(['user_id' => null]);
        
        // Изменяем тип поля с bigint на uuid используя прямой SQL для PostgreSQL
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE uuid USING NULL');

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
