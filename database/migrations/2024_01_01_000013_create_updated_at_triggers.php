<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create function to update updated_at timestamp
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = now();
                RETURN NEW;
            END;
            $$ language plpgsql;
        ');

        // Create triggers for tables with updated_at
        $tables = ['users', 'events', 'bookings', 'seat_instances'];
        
        foreach ($tables as $table) {
            DB::unprepared("
                DROP TRIGGER IF EXISTS update_{$table}_updated_at ON {$table};
                CREATE TRIGGER update_{$table}_updated_at
                    BEFORE UPDATE ON {$table}
                    FOR EACH ROW
                    EXECUTE FUNCTION update_updated_at_column();
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'events', 'bookings', 'seat_instances'];
        
        foreach ($tables as $table) {
            DB::unprepared("DROP TRIGGER IF EXISTS update_{$table}_updated_at ON {$table};");
        }

        DB::unprepared('DROP FUNCTION IF EXISTS update_updated_at_column();');
    }
};

