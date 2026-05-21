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
        Schema::create('shipment_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->string('event_type')->index(); // created, collected, in_transit, out_for_delivery, delivery_attempt, delivered, exception
            $table->text('description')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->string('location')->nullable(); // City/State or address
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('metadata')->nullable(); // Additional data (photo_url, document_id, etc.)
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['shipment_id', 'event_type']);
            $table->index(['shipment_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_timelines');
    }
};











