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
        Schema::create('route_capacity_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained()->onDelete('cascade');
            $table->decimal('offered_weight', 10, 2); // kg
            $table->decimal('offered_volume', 10, 2); // m3
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('price_per_m3', 10, 2);
            $table->decimal('min_price', 10, 2)->default(0);
            $table->string('status')->default('active'); // active, booked, completed, cancelled
            $table->json('restrictions')->nullable(); // restrições físicas em JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_capacity_offers');
    }
};
