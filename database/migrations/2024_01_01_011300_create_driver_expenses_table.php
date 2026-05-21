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
        Schema::create('driver_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('set null');
            $table->string('expense_type'); // toll, fuel, meal, parking, other
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('payment_method')->nullable(); // cash, card, pix, etc.
            $table->string('receipt_url')->nullable(); // Photo/scan of receipt
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable(); // location, etc.
            $table->timestamps();
            
            $table->index(['driver_id', 'expense_date']);
            $table->index(['driver_id', 'status']);
            $table->index(['route_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_expenses');
    }
};






