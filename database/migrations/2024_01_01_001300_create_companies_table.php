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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('cnpj')->unique();
            $table->string('ie')->nullable(); // Inscrição Estadual
            $table->string('im')->nullable(); // Inscrição Municipal
            $table->string('email');
            $table->string('phone');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            
            // Endereço
            $table->string('postal_code');
            $table->string('address');
            $table->string('address_number');
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('Brasil');
            
            // Configurações fiscais
            $table->string('crt')->default('3'); // Código de Regime Tributário
            $table->string('cnae')->nullable(); // CNAE principal
            $table->json('cnae_secondary')->nullable(); // CNAEs secundários
            
            // Configurações do sistema
            $table->json('settings')->nullable(); // Configurações específicas
            $table->boolean('is_active')->default(true);
            $table->boolean('is_matrix')->default(true); // Se é a matriz
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active']);
            $table->index('cnpj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
