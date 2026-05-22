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
        Schema::create('split_billings', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('tenant_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Tenant involved in split');

            $table->foreignId('tenant_invoice_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->comment('Associated tenant invoice');

            // Polymorphic Reference
            $table->string('reference_type')
                ->comment('Type of reference (plan_subscription, invoice_payment, usage)');

            $table->unsignedBigInteger('reference_id')
                ->comment('ID of the referenced resource');

            // Amounts
            $table->decimal('base_amount', 12, 2)
                ->comment('Base amount that split is calculated from');

            $table->decimal('split_percentage', 5, 2)
                ->comment('Percentage used for split calculation');

            $table->decimal('commission_amount', 12, 2)
                ->comment('Commission amount for superadmin');

            // Status
            $table->enum('status', [
                'pending',
                'calculated',
                'invoiced',
                'paid'
            ])->default('pending')
              ->comment('Split billing status');

            // Dates
            $table->date('calculation_date')
                ->comment('When split was calculated');

            $table->date('invoice_date')
                ->nullable()
                ->comment('When invoiced to tenant');

            $table->date('payment_date')
                ->nullable()
                ->comment('When payment received from tenant');

            // Metadata
            $table->json('metadata')
                ->nullable()
                ->comment('Additional tracking data');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['status']);
            $table->index('calculation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('split_billings');
    }
};
