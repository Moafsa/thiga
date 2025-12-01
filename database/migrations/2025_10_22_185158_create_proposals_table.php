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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('salesperson_id')->constrained()->onDelete('cascade');
            $table->string('proposal_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Valores
            $table->decimal('base_value', 10, 2); // Valor base
            $table->decimal('discount_percentage', 5, 2)->default(0.00); // Percentual de desconto aplicado
            $table->decimal('discount_value', 10, 2)->default(0.00); // Valor do desconto
            $table->decimal('final_value', 10, 2); // Valor final após desconto
            
            // Status e datas
            $table->enum('status', ['draft', 'sent', 'negotiating', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // Observações
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable(); // Anexos
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['salesperson_id', 'status']);
            $table->index('proposal_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
