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
        Schema::table('payments', function (Blueprint $table) {
            // Add invoice_id to link payments to invoices (for operational billing)
            $table->foreignId('invoice_id')->nullable()->after('subscription_id')->constrained()->onDelete('cascade');
            
            // Add expense_id to link payments to expenses
            $table->foreignId('expense_id')->nullable()->after('invoice_id')->constrained()->onDelete('cascade');
            
            // Indexes
            $table->index('invoice_id');
            $table->index('expense_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['expense_id']);
            $table->dropColumn(['invoice_id', 'expense_id']);
        });
    }
};






















