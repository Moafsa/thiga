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
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (!Schema::hasColumn('shipments', 'pickup_latitude')) {
                    $table->decimal('pickup_latitude', 10, 8)->nullable()->after('pickup_zip_code');
                }
                
                if (!Schema::hasColumn('shipments', 'pickup_longitude')) {
                    $table->decimal('pickup_longitude', 11, 8)->nullable()->after('pickup_latitude');
                }
                
                if (!Schema::hasColumn('shipments', 'delivery_latitude')) {
                    $table->decimal('delivery_latitude', 10, 8)->nullable()->after('delivery_zip_code');
                }
                
                if (!Schema::hasColumn('shipments', 'delivery_longitude')) {
                    $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
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
                if (Schema::hasColumn('shipments', 'pickup_latitude')) {
                    $table->dropColumn('pickup_latitude');
                }
                
                if (Schema::hasColumn('shipments', 'pickup_longitude')) {
                    $table->dropColumn('pickup_longitude');
                }
                
                if (Schema::hasColumn('shipments', 'delivery_latitude')) {
                    $table->dropColumn('delivery_latitude');
                }
                
                if (Schema::hasColumn('shipments', 'delivery_longitude')) {
                    $table->dropColumn('delivery_longitude');
                }
            });
        }
    }
};
