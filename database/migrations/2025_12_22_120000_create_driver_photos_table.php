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
        Schema::create('driver_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->string('photo_url');
            $table->string('photo_type')->default('profile'); // profile, document, cnh, vehicle, etc.
            $table->text('description')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // width, height, size, etc.
            $table->timestamps();
            
            $table->index(['driver_id', 'photo_type']);
            $table->index(['driver_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_photos');
    }
};





