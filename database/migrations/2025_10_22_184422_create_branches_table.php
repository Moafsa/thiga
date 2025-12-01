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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable(); // Código da filial
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Endereço
            $table->string('postal_code');
            $table->string('address');
            $table->string('address_number');
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('Brasil');
            
            // Configurações
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_operational')->default(true); // Se opera cargas
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'company_id', 'is_active']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
