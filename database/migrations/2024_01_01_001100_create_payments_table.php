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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('asaas_payment_id')->nullable(); // ID do pagamento no Asaas
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled', 'refunded'])->default('pending');
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // credit_card, pix, boleto
            $table->json('asaas_response')->nullable(); // Resposta completa do Asaas
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['subscription_id', 'status']);
            $table->index('asaas_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
