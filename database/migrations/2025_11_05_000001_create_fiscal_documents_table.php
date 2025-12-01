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
        Schema::create('fiscal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Document type and relationships
            $table->enum('document_type', ['cte', 'mdfe'])->index();
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('cascade');
            
            // Mitt API integration
            $table->string('mitt_id')->nullable()->unique();
            $table->string('mitt_number')->nullable()->index();
            $table->string('access_key')->nullable()->unique();
            
            // Status tracking
            $table->enum('status', [
                'pending',           // Initial status, waiting for processing
                'validating',        // Data validation in progress
                'processing',        // Being sent to Mitt
                'authorized',        // Authorized by Sefaz
                'rejected',         // Rejected by Sefaz
                'cancelled',        // Cancelled
                'error'             // Error during processing
            ])->default('pending')->index();
            
            // Document data
            $table->text('xml')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('xml_url')->nullable();
            
            // Error tracking
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->json('mitt_response')->nullable();
            
            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tenant_id', 'document_type', 'status']);
            $table->index(['shipment_id', 'document_type']);
            $table->index(['route_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_documents');
    }
};








