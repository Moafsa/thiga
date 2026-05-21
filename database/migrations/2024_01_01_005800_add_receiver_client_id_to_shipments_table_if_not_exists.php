<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds receiver_client_id and other missing columns to shipments table to match the expected structure.
     */
    public function up(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                // Add receiver_client_id if it doesn't exist
                if (!Schema::hasColumn('shipments', 'receiver_client_id')) {
                    $table->foreignId('receiver_client_id')->nullable()->after('sender_client_id')->constrained('clients')->onDelete('cascade');
                }
                
                // Add missing columns from new structure
                if (!Schema::hasColumn('shipments', 'tracking_number')) {
                    $table->string('tracking_number')->unique()->after('receiver_client_id');
                }
                
                if (!Schema::hasColumn('shipments', 'title')) {
                    $table->string('title')->after('tracking_number');
                }
                
                if (!Schema::hasColumn('shipments', 'pickup_address')) {
                    $table->string('pickup_address')->nullable()->after('title');
                }
                
                if (!Schema::hasColumn('shipments', 'pickup_city')) {
                    $table->string('pickup_city')->nullable()->after('pickup_address');
                }
                
                if (!Schema::hasColumn('shipments', 'pickup_state')) {
                    $table->string('pickup_state')->nullable()->after('pickup_city');
                }
                
                if (!Schema::hasColumn('shipments', 'pickup_zip_code')) {
                    $table->string('pickup_zip_code')->nullable()->after('pickup_state');
                }
                
                if (!Schema::hasColumn('shipments', 'delivery_address')) {
                    $table->string('delivery_address')->nullable()->after('pickup_zip_code');
                }
                
                if (!Schema::hasColumn('shipments', 'delivery_city')) {
                    $table->string('delivery_city')->nullable()->after('delivery_address');
                }
                
                if (!Schema::hasColumn('shipments', 'delivery_state')) {
                    $table->string('delivery_state')->nullable()->after('delivery_city');
                }
                
                if (!Schema::hasColumn('shipments', 'delivery_zip_code')) {
                    $table->string('delivery_zip_code')->nullable()->after('delivery_state');
                }
                
                if (!Schema::hasColumn('shipments', 'pickup_date')) {
                    $table->date('pickup_date')->nullable()->after('delivery_zip_code');
                }
                
                if (!Schema::hasColumn('shipments', 'pickup_time')) {
                    $table->time('pickup_time')->nullable()->after('pickup_date');
                }
                
                if (!Schema::hasColumn('shipments', 'delivery_time')) {
                    $table->time('delivery_time')->nullable()->after('delivery_date');
                }
                
                if (!Schema::hasColumn('shipments', 'value')) {
                    $table->decimal('value', 10, 2)->nullable()->after('goods_value');
                }
                
                if (!Schema::hasColumn('shipments', 'volume')) {
                    $table->decimal('volume', 8, 2)->nullable()->after('weight');
                }
                
                if (!Schema::hasColumn('shipments', 'quantity')) {
                    $table->integer('quantity')->default(1)->after('volume');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop columns as they might be in use
    }
};
