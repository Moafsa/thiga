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
        Schema::table('routes', function (Blueprint $table) {
            // Drop the existing foreign key constraint and index
            $table->dropForeign(['driver_id']);
            // Note: We can't drop index here if it's used by foreign key, so we'll handle it differently
            
            // Use DB::statement for PostgreSQL to alter column
            DB::statement('ALTER TABLE routes ALTER COLUMN driver_id DROP NOT NULL');
            
            // Re-add the foreign key constraint with onDelete('set null')
            $table->foreign('driver_id')
                  ->references('id')
                  ->on('drivers')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['driver_id']);
            
            // Use DB::statement for PostgreSQL to alter column back to NOT NULL
            // First, update any NULL values to a default driver (or handle differently)
            DB::statement('ALTER TABLE routes ALTER COLUMN driver_id SET NOT NULL');
            
            // Re-add the foreign key constraint with onDelete('cascade')
            $table->foreign('driver_id')
                  ->references('id')
                  ->on('drivers')
                  ->onDelete('cascade');
        });
    }
};

