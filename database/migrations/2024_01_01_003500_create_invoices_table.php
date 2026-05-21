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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            // Invoice identification
            $table->string('invoice_number')->unique();
            $table->date('issue_date');
            $table->date('due_date');
            
            // Financial data
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            // Status
            $table->enum('status', ['open', 'paid', 'overdue', 'cancelled'])->default('open');
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'client_id']);
            $table->index('due_date');
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};






















