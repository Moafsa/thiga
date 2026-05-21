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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('expense_category_id')->nullable()->constrained()->onDelete('set null');
            
            // Expense details
            $table->string('description');
            $table->decimal('amount', 10, 2);
            
            // Dates
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'paid'])->default('pending');
            
            // Payment information
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'expense_category_id']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};






















