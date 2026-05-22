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
        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('tenant_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Tenant being billed');

            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->comment('Related subscription (if plan-based)');

            // Invoice Identifier
            $table->string('invoice_number')
                ->unique()
                ->comment('Unique invoice number (TI-YYYY-MM-XXXXXX)');

            $table->enum('type', ['plan_subscription', 'usage_overage', 'adjustment'])
                ->default('plan_subscription')
                ->comment('Type of invoice');

            // Amounts
            $table->decimal('base_amount', 12, 2)
                ->comment('Base amount (e.g., plan price)');

            $table->decimal('split_percentage', 5, 2)
                ->comment('Split percentage applied');

            $table->decimal('split_amount', 12, 2)
                ->comment('Commission amount for superadmin');

            $table->decimal('total_amount', 12, 2)
                ->comment('Total amount tenant must pay');

            // Period
            $table->date('period_start')
                ->comment('Start of billing period');

            $table->date('period_end')
                ->comment('End of billing period');

            $table->date('issue_date')
                ->comment('Invoice issue date');

            $table->date('due_date')
                ->comment('Payment due date');

            // Status
            $table->enum('status', [
                'draft',
                'issued',
                'sent',
                'paid',
                'overdue',
                'cancelled'
            ])->default('draft')
              ->comment('Invoice status');

            // Asaas References
            $table->string('asaas_invoice_id')
                ->nullable()
                ->comment('Asaas invoice ID');

            $table->string('asaas_payment_id')
                ->nullable()
                ->comment('Asaas payment ID');

            // Metadata
            $table->json('metadata')
                ->nullable()
                ->comment('Additional data (plan_name, features, etc)');

            // Timestamps
            $table->timestamps();

            // Indexes for common queries
            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index(['asaas_payment_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invoices');
    }
};
