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
        Schema::table('expenses', function (Blueprint $table) {
            // Fields for fuel refueling tracking
            $table->decimal('fuel_liters', 10, 2)->nullable()->after('amount');
            $table->integer('odometer_reading')->nullable()->after('fuel_liters');
            $table->decimal('price_per_liter', 8, 4)->nullable()->after('odometer_reading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['fuel_liters', 'odometer_reading', 'price_per_liter']);
        });
    }
};

