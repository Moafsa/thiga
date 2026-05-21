<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds driver_id column to shipments table if it doesn't exist.
     * The migration 2025_10_22_190450_create_shipments_table.php checks if the table exists,
     * so it may not have created the driver_id column if the table already existed.
     */
    public function up(): void
    {
        if (Schema::hasTable('shipments') && !Schema::hasColumn('shipments', 'driver_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->foreignId('driver_id')->nullable()->after('route_id')->constrained()->onDelete('set null');
                $table->index(['driver_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'driver_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropForeign(['driver_id']);
                $table->dropIndex(['driver_id', 'status']);
                $table->dropColumn('driver_id');
            });
        }
    }
};
