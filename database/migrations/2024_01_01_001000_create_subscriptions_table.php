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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('asaas_customer_id')->nullable(); // ID do cliente no Asaas
            $table->string('asaas_subscription_id')->nullable(); // ID da assinatura no Asaas
            $table->enum('status', ['active', 'inactive', 'suspended', 'cancelled', 'trial'])->default('trial');
            $table->date('trial_ends_at')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('billing_cycle', 20); // monthly, yearly
            $table->json('features')->nullable(); // Features específicas do plano
            $table->json('limits')->nullable(); // Limites do plano (usuários, cargas, etc)
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('asaas_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
