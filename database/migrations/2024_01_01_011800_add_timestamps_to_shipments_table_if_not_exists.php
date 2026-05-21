<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds picked_up_at and delivered_at columns to shipments table if they don't exist.
     */
    public function up(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                // Add picked_up_at if it doesn't exist
                if (!Schema::hasColumn('shipments', 'picked_up_at')) {
                    $table->timestamp('picked_up_at')->nullable()->after('status');
                }
                
                // Add delivered_at if it doesn't exist
                if (!Schema::hasColumn('shipments', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (Schema::hasColumn('shipments', 'delivered_at')) {
                    $table->dropColumn('delivered_at');
                }
                
                if (Schema::hasColumn('shipments', 'picked_up_at')) {
                    $table->dropColumn('picked_up_at');
                }
            });
        }
    }
};
