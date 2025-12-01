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
        Schema::create('location_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('cascade');
            
            // Coordenadas
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable(); // Precisão em metros
            $table->decimal('altitude', 8, 2)->nullable(); // Altitude em metros
            $table->decimal('speed', 8, 2)->nullable(); // Velocidade em km/h
            
            // Direção e movimento
            $table->decimal('heading', 5, 2)->nullable(); // Direção em graus
            $table->boolean('is_moving')->default(false);
            
            // Timestamp
            $table->timestamp('tracked_at');
            
            // Metadados
            $table->string('device_id')->nullable();
            $table->string('app_version')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'driver_id', 'tracked_at']);
            $table->index(['route_id', 'tracked_at']);
            $table->index(['shipment_id', 'tracked_at']);
            $table->index('tracked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_trackings');
    }
};
