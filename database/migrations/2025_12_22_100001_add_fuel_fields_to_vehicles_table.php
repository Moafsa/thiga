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
        if (!Schema::hasColumn('vehicles', 'fuel_type')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('fuel_type', 50)->nullable()->after('vehicle_type'); // diesel, gasoline, ethanol, cng
            });
        }

        if (!Schema::hasColumn('vehicles', 'fuel_consumption_per_km')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->decimal('fuel_consumption_per_km', 8, 4)->nullable()->after('fuel_type'); // Liters per km
            });
        }

        if (!Schema::hasColumn('vehicles', 'tank_capacity')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->decimal('tank_capacity', 8, 2)->nullable()->after('fuel_consumption_per_km'); // Liters
            });
        }

        if (!Schema::hasColumn('vehicles', 'average_fuel_consumption')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->decimal('average_fuel_consumption', 8, 4)->nullable()->after('tank_capacity'); // Average consumption (backward compatibility)
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'fuel_type',
            'fuel_consumption_per_km',
            'tank_capacity',
            'average_fuel_consumption',
        ];

        $existing = array_filter($columns, fn($column) => Schema::hasColumn('vehicles', $column));

        if (!empty($existing)) {
            Schema::table('vehicles', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};



















