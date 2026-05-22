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
        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Responsible salesperson/operator
            $table->foreignId('crm_stage_id')->constrained()->onDelete('cascade');
            $table->string('title'); // e.g. "Cotação para SP"
            $table->decimal('lead_value', 12, 2)->nullable();
            $table->enum('status', ['open', 'won', 'lost'])->default('open');
            $table->string('contact_channel')->default('whatsapp'); // whatsapp, email, phone
            $table->date('next_action_date')->nullable(); // For SLA/Alerts
            $table->timestamp('last_contacted_at')->nullable();
            $table->text('ai_summary')->nullable(); // Insight from AI
            $table->json('custom_data')->nullable(); // Flexibility
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_deals');
    }
};
