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
        Schema::table('routes', function (Blueprint $table) {
            // Time control - Planned by router
            $table->datetime('planned_departure_datetime')->nullable()->after('scheduled_date');
            $table->datetime('planned_arrival_datetime')->nullable()->after('planned_departure_datetime');
            
            // Time control - Actual by driver
            $table->datetime('actual_departure_datetime')->nullable()->after('planned_arrival_datetime');
            $table->datetime('actual_arrival_datetime')->nullable()->after('actual_departure_datetime');
            
            // Driver per diem control
            $table->integer('driver_diarias_count')->nullable()->default(0)->after('actual_arrival_datetime');
            $table->decimal('driver_diaria_value', 10, 2)->nullable()->default(0)->after('driver_diarias_count');
            
            // Deposit control
            $table->decimal('deposit_toll', 10, 2)->nullable()->default(0)->after('driver_diaria_value');
            $table->decimal('deposit_expenses', 10, 2)->nullable()->default(0)->after('deposit_toll');
            $table->decimal('deposit_fuel', 10, 2)->nullable()->default(0)->after('deposit_expenses');
            
            // Total revenue (calculated from CT-e values)
            $table->decimal('total_revenue', 10, 2)->nullable()->after('deposit_fuel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn([
                'planned_departure_datetime',
                'planned_arrival_datetime',
                'actual_departure_datetime',
                'actual_arrival_datetime',
                'driver_diarias_count',
                'driver_diaria_value',
                'deposit_toll',
                'deposit_expenses',
                'deposit_fuel',
                'total_revenue',
            ]);
        });
    }
};




