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
        Schema::create('toll_plazas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the toll plaza
            $table->string('highway')->nullable(); // Highway name (ex: BR-101, SP-348)
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Toll prices by vehicle type (in BRL)
            $table->decimal('price_car', 10, 2)->default(0); // Car (automóvel)
            $table->decimal('price_van', 10, 2)->default(0); // Van
            $table->decimal('price_truck_2_axles', 10, 2)->default(0); // Truck with 2 axles
            $table->decimal('price_truck_3_axles', 10, 2)->default(0); // Truck with 3 axles
            $table->decimal('price_truck_4_axles', 10, 2)->default(0); // Truck with 4 axles
            $table->decimal('price_truck_5_axles', 10, 2)->default(0); // Truck with 5+ axles
            $table->decimal('price_bus', 10, 2)->default(0); // Bus
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['latitude', 'longitude']);
            $table->index(['state', 'city']);
            $table->index('highway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toll_plazas');
    }
};






























