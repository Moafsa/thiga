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
            // Planned path - the route calculated by Google Maps (street by street)
            $table->json('planned_path')->nullable()->after('route_options');
            
            // Actual path - the real path taken by the driver (street by street)
            $table->json('actual_path')->nullable()->after('planned_path');
            
            // Last path update timestamp
            $table->timestamp('path_updated_at')->nullable()->after('actual_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn(['planned_path', 'actual_path', 'path_updated_at']);
        });
    }
};

