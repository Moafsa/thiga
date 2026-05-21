<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('route_expense_id');
            $table->unsignedBigInteger('shipment_id');
            $table->unsignedBigInteger('route_id');

            // Valor calculado e rateado para este CTe
            $table->decimal('allocated_amount', 12, 2);

            // Percentual de participação usado no rateio (ex: 0.6000 = 60,00%)
            $table->decimal('allocation_pct', 7, 4)->default(0);

            // Descrição da base de cálculo para auditoria/transparência
            // Ex: "Valor CTe: R$ 1.200,00 / Total Rota: R$ 2.000,00 (60,00%)"
            $table->string('allocation_basis', 400)->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('route_expense_id')->references('id')->on('route_expenses')->onDelete('cascade');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');

            // Um CTe só pode receber um rateio por evento de custo
            $table->unique(['route_expense_id', 'shipment_id']);

            $table->index(['tenant_id', 'shipment_id']);
            $table->index(['tenant_id', 'route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_cost_allocations');
    }
};
