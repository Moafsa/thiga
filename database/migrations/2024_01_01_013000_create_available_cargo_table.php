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
        Schema::create('available_cargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['available', 'assigned', 'collected', 'cancelled'])->default('available');
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['proposal_id']);
            $table->index(['route_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_cargo');
    }
};
