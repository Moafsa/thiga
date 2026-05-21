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
            $table->foreignId('vehicle_id')->nullable()->after('driver_id')->constrained()->onDelete('set null');
            $table->index(['vehicle_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropIndex(['vehicle_id', 'scheduled_date']);
            $table->dropColumn('vehicle_id');
        });
    }
};

