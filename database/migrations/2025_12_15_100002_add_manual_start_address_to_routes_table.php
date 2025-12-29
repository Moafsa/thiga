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
            // Manual start address fields (when not using branch)
            $table->string('start_address')->nullable()->after('branch_id');
            $table->string('start_city')->nullable()->after('start_address');
            $table->string('start_state', 2)->nullable()->after('start_city');
            $table->string('start_zip_code')->nullable()->after('start_state');
            $table->string('start_address_type')->nullable()->after('start_zip_code'); // 'branch', 'current_location', 'manual'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn(['start_address', 'start_city', 'start_state', 'start_zip_code', 'start_address_type']);
        });
    }
};






























