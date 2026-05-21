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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            
            // Status da rota
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Coordenadas da rota
            $table->decimal('start_latitude', 10, 8)->nullable();
            $table->decimal('start_longitude', 11, 8)->nullable();
            $table->decimal('end_latitude', 10, 8)->nullable();
            $table->decimal('end_longitude', 11, 8)->nullable();
            
            // Distância e tempo estimado
            $table->decimal('estimated_distance', 8, 2)->nullable(); // em km
            $table->integer('estimated_duration')->nullable(); // em minutos
            
            // Configurações
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['driver_id', 'scheduled_date']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
