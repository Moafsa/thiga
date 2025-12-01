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
        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->string('proof_type'); // delivery, pickup, damage, etc.
            $table->text('description')->nullable();
            
            // Localização da entrega
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            
            // Fotos e documentos
            $table->json('photos')->nullable(); // Array de URLs das fotos
            $table->json('documents')->nullable(); // Array de URLs dos documentos
            
            // Dados da entrega
            $table->string('recipient_name')->nullable();
            $table->string('recipient_document')->nullable();
            $table->string('recipient_signature')->nullable(); // Base64 da assinatura
            $table->timestamp('delivery_time');
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            
            // Metadados
            $table->json('metadata')->nullable(); // Dados adicionais
            $table->string('device_info')->nullable(); // Info do dispositivo
            $table->string('app_version')->nullable(); // Versão do app
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'shipment_id']);
            $table->index(['driver_id', 'delivery_time']);
            $table->index('proof_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_proofs');
    }
};
