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
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->string('fuel_type', 50); // diesel, gasoline, ethanol, cng
            $table->decimal('price_per_liter', 10, 4); // Price in BRL
            $table->date('effective_date'); // When this price becomes effective
            $table->date('expires_at')->nullable(); // When this price expires (null = current)
            $table->string('region', 2)->nullable(); // State code (SP, RJ, etc) - null = national
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['fuel_type', 'is_active', 'effective_date']);
            $table->index(['region', 'fuel_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};


























