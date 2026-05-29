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
        Schema::create('route_space_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('booking_uuid')->unique();
            $table->foreignId('owner_tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('booker_tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('route_capacity_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('set null'); // link opcional com a carga de B
            
            // Proxy Operacional da Carga (para isolamento fiscal e de CRM)
            $table->string('cargo_title');
            $table->decimal('booked_weight', 10, 2);
            $table->decimal('booked_volume', 10, 2);
            $table->string('pickup_city');
            $table->string('pickup_state');
            $table->string('delivery_city');
            $table->string('delivery_state');
            
            // Status do Compartilhamento
            $table->string('status')->default('pending_approval'); // pending_approval, approved, rejected, cargo_received, in_transit, delivered, cancelled
            
            // Controle Financeiro
            $table->decimal('amount_base', 10, 2);
            $table->decimal('amount_detour_cost', 10, 2);
            $table->decimal('amount_platform_fee', 10, 2);
            $table->decimal('amount_final', 10, 2);
            $table->string('payment_status')->default('pending'); // pending, paid, refunded, dispute
            $table->string('asaas_payment_id')->nullable();
            $table->string('matching_link_token')->unique(); // token seguro de sync de timeline
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_space_bookings');
    }
};
