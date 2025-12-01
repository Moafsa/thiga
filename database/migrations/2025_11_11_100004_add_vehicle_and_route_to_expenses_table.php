<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campos vehicle_id e route_id à tabela expenses para vincular despesas
     * a veículos (manutenções) e rotas (despesas por rota).
     * 
     * IMPORTANTE: Apenas veículos do tipo 'fleet' podem receber despesas vinculadas.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('expense_category_id')->constrained()->onDelete('set null');
            $table->foreignId('route_id')->nullable()->after('vehicle_id')->constrained()->onDelete('set null');
            
            // Indexes
            $table->index(['tenant_id', 'vehicle_id']);
            $table->index(['tenant_id', 'route_id']);
            $table->index(['vehicle_id', 'expense_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['route_id']);
            $table->dropIndex(['tenant_id', 'vehicle_id']);
            $table->dropIndex(['tenant_id', 'route_id']);
            $table->dropIndex(['vehicle_id', 'expense_category_id']);
            $table->dropColumn(['vehicle_id', 'route_id']);
        });
    }
};

