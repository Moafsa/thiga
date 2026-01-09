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
        Schema::create('maps_api_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('provider'); // 'mapbox' or 'google'
            $table->string('operation'); // 'geocode', 'route', etc.
            $table->date('date');
            $table->integer('requests')->default(0);
            $table->integer('successful')->default(0);
            $table->integer('failed')->default(0);
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['provider', 'date']);
            $table->unique(['tenant_id', 'user_id', 'provider', 'operation', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maps_api_usages');
    }
};
