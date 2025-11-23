<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        
        // Очищаем данные user_id, так как они несовместимы с UUID
        DB::table('sessions')->whereNotNull('user_id')->update(['user_id' => null]);
        
        // Изменяем тип поля с bigint на uuid используя прямой SQL для PostgreSQL
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE uuid USING NULL');
        
        Schema::table('sessions', function (Blueprint $table) {
            // Добавляем внешний ключ
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Удаляем внешний ключ
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('sessions', function (Blueprint $table) {
            // Возвращаем тип поля обратно на bigint
            $table->bigInteger('user_id')->nullable()->change();
        });
    }
};
