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
        Schema::table('shipments', function (Blueprint $table) {
            // Add shipment type: 'delivery' (entrega) or 'pickup' (coleta)
            // Using string instead of enum for PostgreSQL compatibility
            $table->string('shipment_type', 20)->default('delivery')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('shipment_type');
        });
    }
};
