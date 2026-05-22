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
        Schema::table('tenants', function (Blueprint $table) {
            // Configuração Asaas do PRÓPRIO TENANT (para cobrar seus clientes)
            // Deixar nullable para compatibilidade com tenants antigos

            $table->string('asaas_api_key')
                ->nullable()
                ->after('asaas_customer_id')
                ->comment('API Key do Asaas do tenant (para cobrar clientes)');

            $table->string('asaas_webhook_token')
                ->nullable()
                ->after('asaas_api_key')
                ->comment('Webhook Token do Asaas do tenant');

            $table->string('asaas_account_id')
                ->nullable()
                ->after('asaas_webhook_token')
                ->comment('Account ID no Asaas do tenant');

            // Flag para saber qual Asaas usar
            $table->boolean('uses_own_asaas')
                ->default(false)
                ->after('asaas_account_id')
                ->comment('Se tenant usa seu próprio Asaas ou superadmin');

            // Dados bancários para recebimento (se usando Asaas)
            $table->json('bank_account_config')
                ->nullable()
                ->after('uses_own_asaas')
                ->comment('Configuração bancária para recebimento Asaas');

            // Índices para queries frequentes
            $table->index('uses_own_asaas');
            $table->index('asaas_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['uses_own_asaas']);
            $table->dropIndex(['asaas_account_id']);

            $table->dropColumn([
                'asaas_api_key',
                'asaas_webhook_token',
                'asaas_account_id',
                'uses_own_asaas',
                'bank_account_config'
            ]);
        });
    }
};
