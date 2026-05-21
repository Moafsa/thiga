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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Basic vehicle information
            $table->string('plate')->unique(); // Placa do veículo (formato brasileiro)
            $table->string('renavam')->nullable()->unique(); // RENAVAM
            $table->string('chassis')->nullable(); // Chassi
            $table->string('brand')->nullable(); // Marca
            $table->string('model')->nullable(); // Modelo
            $table->integer('year')->nullable(); // Ano
            $table->string('color')->nullable(); // Cor
            $table->string('fuel_type')->nullable(); // Tipo de combustível (Gasolina, Diesel, Etanol, etc.)
            
            // Vehicle specifications
            $table->string('vehicle_type')->nullable(); // Tipo (Caminhão, Carreta, Van, etc.)
            $table->decimal('capacity_weight', 10, 2)->nullable(); // Capacidade de peso (kg)
            $table->decimal('capacity_volume', 10, 2)->nullable(); // Capacidade de volume (m³)
            $table->integer('axles')->nullable(); // Número de eixos
            
            // Status and availability
            $table->enum('status', ['available', 'in_use', 'maintenance', 'inactive'])->default('available');
            $table->boolean('is_active')->default(true);
            
            // Insurance and documentation
            $table->date('insurance_expiry_date')->nullable(); // Vencimento do seguro
            $table->date('inspection_expiry_date')->nullable(); // Vencimento da vistoria
            $table->date('registration_expiry_date')->nullable(); // Vencimento do licenciamento
            
            // Odometer and maintenance
            $table->bigInteger('current_odometer')->default(0); // Odômetro atual (km)
            $table->bigInteger('last_maintenance_odometer')->nullable(); // Odômetro da última manutenção
            $table->date('last_maintenance_date')->nullable(); // Data da última manutenção
            $table->integer('maintenance_interval_km')->nullable()->default(10000); // Intervalo de manutenção (km)
            $table->integer('maintenance_interval_days')->nullable()->default(90); // Intervalo de manutenção (dias)
            
            // Additional information
            $table->text('notes')->nullable(); // Observações
            $table->json('metadata')->nullable(); // Dados adicionais
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'is_active']);
            $table->index('plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

