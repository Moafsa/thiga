<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Pivot table for many-to-many relationship between drivers and vehicles.
     * One vehicle can be driven by multiple drivers, and one driver can drive multiple vehicles.
     */
    public function up(): void
    {
        Schema::create('driver_vehicle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            
            // Additional information about the relationship
            $table->date('assigned_at')->default(now()); // When driver was assigned to vehicle
            $table->date('unassigned_at')->nullable(); // When driver was unassigned (soft delete)
            $table->boolean('is_active')->default(true); // Active assignment
            
            // Permissions/restrictions for this driver-vehicle combination
            $table->boolean('can_drive')->default(true); // Driver can drive this vehicle
            $table->text('notes')->nullable(); // Notes about this assignment
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['driver_id', 'vehicle_id', 'is_active'], 'unique_active_assignment');
            $table->index(['driver_id', 'is_active']);
            $table->index(['vehicle_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_vehicle');
    }
};

