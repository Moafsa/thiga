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
        Schema::create('freight_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Identificação da tabela
            $table->string('name'); // Nome da tabela (ex: "Tabela SP-MG")
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Se é a tabela padrão do tenant
            
            // Destino da tabela (pode ser região, cidade, ou CEP range)
            $table->string('destination_type')->default('city'); // city, region, cep_range
            $table->string('destination_name'); // Nome do destino (ex: "BELO HORIZONTE - MG")
            $table->string('destination_state')->nullable(); // Estado (MG, SP, etc)
            $table->string('cep_range_start')->nullable(); // CEP inicial do range
            $table->string('cep_range_end')->nullable(); // CEP final do range
            
            // Valores por faixa de peso (até 100kg)
            $table->decimal('weight_0_30', 10, 2)->nullable(); // De 0 a 30kg
            $table->decimal('weight_31_50', 10, 2)->nullable(); // De 31 a 50kg
            $table->decimal('weight_51_70', 10, 2)->nullable(); // De 51 a 70kg
            $table->decimal('weight_71_100', 10, 2)->nullable(); // De 71 a 100kg
            
            // Valores para acima de 100kg
            $table->decimal('weight_over_100_rate', 10, 4)->nullable(); // Taxa por kg acima de 100kg
            $table->decimal('ctrc_tax', 10, 2)->nullable(); // Taxa CTRC para acima de 100kg
            
            // Configurações gerais de cálculo
            $table->decimal('ad_valorem_rate', 5, 4)->default(0.0040); // 0,40% padrão
            $table->decimal('gris_rate', 5, 4)->default(0.0030); // 0,30% padrão
            $table->decimal('gris_minimum', 10, 2)->default(8.70); // Mínimo GRIS
            $table->decimal('toll_per_100kg', 10, 2)->default(12.95); // Pedágio por 100kg
            $table->decimal('cubage_factor', 8, 2)->default(300); // Fator de cubagem (kg/m³)
            $table->decimal('min_freight_rate_vs_nf', 5, 4)->default(0.01); // 1% mínimo vs NF
            
            // Taxas adicionais (opcionais)
            $table->decimal('tde_markets', 10, 2)->nullable(); // TDE Mercados
            $table->decimal('tde_supermarkets_cd', 10, 2)->nullable(); // TDE CD Supermercados
            $table->decimal('palletization', 10, 2)->nullable(); // Paletização por pallet
            $table->decimal('unloading_tax', 10, 2)->nullable(); // Taxa de descarga
            $table->decimal('weekend_holiday_rate', 5, 4)->default(0.30); // 30% sábado/domingo/feriado
            $table->decimal('redelivery_rate', 5, 4)->default(0.50); // 50% reentrega
            $table->decimal('return_rate', 5, 4)->default(1.00); // 100% devolução
            
            // Configurações
            $table->json('settings')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active', 'is_default']);
            $table->index(['destination_type', 'destination_state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freight_tables');
    }
};





















