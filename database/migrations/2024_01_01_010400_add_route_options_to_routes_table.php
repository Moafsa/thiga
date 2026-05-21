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
            // Branch (Depósito/Filial) de partida
            $table->foreignId('branch_id')->nullable()->after('tenant_id')->constrained()->onDelete('set null');
            
            // Opções de rotas alternativas (até 3)
            $table->json('route_options')->nullable()->after('settings');
            
            // Qual rota foi selecionada (1, 2 ou 3)
            $table->integer('selected_route_option')->nullable()->after('route_options');
            
            // Se a rota foi bloqueada (escolhida e não pode mais ser alterada)
            $table->boolean('is_route_locked')->default(false)->after('selected_route_option');
            
            $table->index(['branch_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id', 'tenant_id']);
            $table->dropColumn(['branch_id', 'route_options', 'selected_route_option', 'is_route_locked']);
        });
    }
};

