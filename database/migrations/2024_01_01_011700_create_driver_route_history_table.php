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
        Schema::create('driver_route_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            
            // Informações básicas da rota (snapshot no momento da conclusão)
            $table->string('route_name');
            $table->text('route_description')->nullable();
            $table->date('scheduled_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at');
            
            // Status e tipo
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->enum('route_type', ['delivery', 'pickup', 'mixed'])->default('mixed');
            
            // Estatísticas de performance
            $table->integer('total_shipments')->default(0);
            $table->integer('delivered_shipments')->default(0);
            $table->integer('picked_up_shipments')->default(0);
            $table->integer('exception_shipments')->default(0);
            
            // Distâncias e tempos
            $table->decimal('planned_distance_km', 10, 2)->nullable(); // Distância planejada
            $table->decimal('actual_distance_km', 10, 2)->nullable(); // Distância real percorrida
            $table->integer('planned_duration_minutes')->nullable(); // Tempo planejado
            $table->integer('actual_duration_minutes')->nullable(); // Tempo real (started_at até completed_at)
            $table->integer('stops_count')->default(0); // Número de paradas
            
            // Eficiência e performance
            $table->decimal('efficiency_score', 5, 2)->nullable(); // Score de 0-100 baseado em desvios, tempo, etc
            $table->decimal('average_speed_kmh', 6, 2)->nullable(); // Velocidade média
            $table->decimal('fuel_efficiency_km_l', 6, 2)->nullable(); // Eficiência de combustível (se disponível)
            
            // Coordenadas
            $table->decimal('start_latitude', 10, 8)->nullable();
            $table->decimal('start_longitude', 11, 8)->nullable();
            $table->decimal('end_latitude', 10, 8)->nullable();
            $table->decimal('end_longitude', 11, 8)->nullable();
            
            // Caminho percorrido (snapshot do actual_path)
            $table->json('actual_path_snapshot')->nullable(); // Array de coordenadas do caminho real
            $table->json('planned_path_snapshot')->nullable(); // Array de coordenadas do caminho planejado
            
            // Desvios e problemas
            $table->decimal('total_deviation_km', 8, 2)->default(0); // Total de desvios da rota planejada
            $table->integer('deviation_count')->default(0); // Número de vezes que desviou
            $table->integer('stops_duration_minutes')->default(0); // Tempo total parado
            
            // Financeiro (snapshot)
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('driver_diarias_amount', 10, 2)->default(0);
            $table->decimal('total_expenses', 10, 2)->default(0);
            $table->decimal('net_profit', 10, 2)->default(0);
            
            // Badges e conquistas (JSON array de badges conquistados)
            $table->json('achievements')->nullable(); // Ex: ['on_time', 'perfect_route', 'high_efficiency']
            
            // Metadados e notas
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Dados extras como clima, trânsito, etc
            
            // Índices para performance
            $table->timestamps();
            
            $table->index(['driver_id', 'completed_at']);
            $table->index(['tenant_id', 'driver_id', 'completed_at']);
            $table->index(['route_id']);
            $table->index('completed_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_route_history');
    }
};
