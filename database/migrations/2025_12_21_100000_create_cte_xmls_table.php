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
        Schema::create('cte_xmls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // CT-e identification
            $table->string('cte_number')->index();
            $table->string('access_key')->nullable()->unique();
            
            // XML storage
            $table->text('xml')->nullable();
            $table->string('xml_url')->nullable();
            
            // Usage tracking
            $table->boolean('is_used')->default(false)->index();
            $table->timestamp('used_at')->nullable();
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tenant_id', 'is_used']);
            $table->index(['tenant_id', 'cte_number']);
            $table->unique(['tenant_id', 'cte_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cte_xmls');
    }
};


























