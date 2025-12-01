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
            return;
        }

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sender_client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('receiver_client_id')->constrained('clients')->onDelete('cascade');
            
            // Dados da carga
            $table->string('tracking_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('weight', 8, 2)->nullable(); // Peso em kg
            $table->decimal('volume', 8, 2)->nullable(); // Volume em m³
            $table->integer('quantity')->default(1);
            $table->decimal('value', 10, 2)->nullable(); // Valor declarado
            
            // Endereços
            $table->string('pickup_address');
            $table->string('pickup_city');
            $table->string('pickup_state');
            $table->string('pickup_zip_code');
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            
            $table->string('delivery_address');
            $table->string('delivery_city');
            $table->string('delivery_state');
            $table->string('delivery_zip_code');
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            
            // Datas
            $table->date('pickup_date');
            $table->time('pickup_time');
            $table->date('delivery_date');
            $table->time('delivery_time');
            
            // Status
            $table->enum('status', [
                'pending', 'scheduled', 'picked_up', 'in_transit', 
                'delivered', 'returned', 'cancelled'
            ])->default('pending');
            
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Observações
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['route_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('tracking_number');
            $table->index('pickup_date');
            $table->index('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::drop('shipments');
        }
    }
};
