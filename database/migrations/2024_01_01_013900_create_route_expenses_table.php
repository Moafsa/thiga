<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('route_id');
            // Quando o custo é direto de um único CTe (shipment), preenchido opcionalmente
            $table->unsignedBigInteger('shipment_id')->nullable();

            $table->enum('cost_type', [
                'coleta',           // Custo de coleta da carga
                'transferencia',    // Transferência entre terminais
                'entrega',          // Custo de entrega final
                'pedagio',          // Pedágio (frota própria)
                'combustivel',      // Combustível (frota própria)
                'diaria_motorista', // Diária do motorista (frota própria)
                'chapa_carga',      // Chapa no carregamento
                'chapa_descarga',   // Chapa no descarregamento
                'arrumador',        // Arrumador
                'avaria',           // Avaria na carga (descrição obrigatória)
                'emissao_cte',      // Custo de emissão do CTe (software/contador)
                'imposto_icms',     // ICMS sobre frete
                'imposto_iss',      // ISS sobre serviço
                'frete_terceiro',   // Valor total pago ao transportador terceiro
                'extra',            // Outros (descrição obrigatória)
            ]);

            $table->string('description', 500)->nullable(); // Obrigatório para avaria e extra
            $table->decimal('amount', 12, 2);               // Valor total do custo

            $table->enum('allocation_method', [
                'proporcional_valor',  // % do valor do CTe / total da rota (padrão)
                'proporcional_peso',   // % do peso do CTe / total da rota
                'proporcional_volume', // % do volume do CTe / total da rota
                'igualitario',         // Divide igualmente entre todos os CTes da rota
                'direto',              // Sem rateio — custo já é do shipment_id informado
            ])->default('proporcional_valor');

            // Motorista próprio ou terceiro
            $table->enum('operator_type', ['proprio', 'terceiro'])->default('proprio');
            $table->string('third_party_name', 200)->nullable();          // Nome da transportadora
            $table->unsignedBigInteger('third_party_cte_xml_id')->nullable(); // CteXml do terceiro

            // Rótulo do trecho (ex: "Caxias do Sul→SP", "SP→Contagem")
            $table->string('leg', 150)->nullable();

            $table->text('notes')->nullable();
            $table->string('receipt_url')->nullable(); // Comprovante/nota fiscal do custo

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('set null');

            $table->index(['tenant_id', 'route_id']);
            $table->index(['tenant_id', 'shipment_id']);
            $table->index('cost_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_expenses');
    }
};
