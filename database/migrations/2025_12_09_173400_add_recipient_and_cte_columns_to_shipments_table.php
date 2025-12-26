<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds recipient and CT-e columns to shipments table.
     */
    public function up(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                // Add recipient columns if they don't exist
                if (!Schema::hasColumn('shipments', 'recipient_name')) {
                    $table->string('recipient_name')->nullable()->after('receiver_client_id');
                }
                
                if (!Schema::hasColumn('shipments', 'recipient_address')) {
                    $table->string('recipient_address')->nullable()->after('recipient_name');
                }
                
                if (!Schema::hasColumn('shipments', 'recipient_city')) {
                    $table->string('recipient_city')->nullable()->after('recipient_address');
                }
                
                if (!Schema::hasColumn('shipments', 'recipient_state')) {
                    $table->string('recipient_state')->nullable()->after('recipient_city');
                }
                
                if (!Schema::hasColumn('shipments', 'recipient_zip_code')) {
                    $table->string('recipient_zip_code')->nullable()->after('recipient_state');
                }
                
                if (!Schema::hasColumn('shipments', 'recipient_phone')) {
                    $table->string('recipient_phone')->nullable()->after('recipient_zip_code');
                }
                
                // Add CT-e columns if they don't exist
                if (!Schema::hasColumn('shipments', 'cte_number')) {
                    $table->string('cte_number')->nullable()->index()->after('recipient_phone');
                }
                
                if (!Schema::hasColumn('shipments', 'cte_status')) {
                    $table->string('cte_status')->nullable()->after('cte_number');
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
                if (Schema::hasColumn('shipments', 'recipient_phone')) {
                    $table->dropColumn('recipient_phone');
                }
                if (Schema::hasColumn('shipments', 'recipient_zip_code')) {
                    $table->dropColumn('recipient_zip_code');
                }
                if (Schema::hasColumn('shipments', 'recipient_state')) {
                    $table->dropColumn('recipient_state');
                }
                if (Schema::hasColumn('shipments', 'recipient_city')) {
                    $table->dropColumn('recipient_city');
                }
                if (Schema::hasColumn('shipments', 'recipient_address')) {
                    $table->dropColumn('recipient_address');
                }
                if (Schema::hasColumn('shipments', 'recipient_name')) {
                    $table->dropColumn('recipient_name');
                }
                if (Schema::hasColumn('shipments', 'cte_status')) {
                    $table->dropColumn('cte_status');
                }
                if (Schema::hasColumn('shipments', 'cte_number')) {
                    $table->dropColumn('cte_number');
                }
            });
        }
    }
};


















