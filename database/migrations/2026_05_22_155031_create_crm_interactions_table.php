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
        Schema::create('crm_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('crm_deal_id')->constrained()->onDelete('cascade');
            $table->string('type'); // whatsapp, note, call, ai_insight
            $table->text('content'); // The message or note text
            $table->string('sender_type'); // client, user, ai, system
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // if sender_type == user
            $table->json('metadata')->nullable(); // For wuzapi message_id, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_interactions');
    }
};
