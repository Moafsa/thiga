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
        Schema::create('whatsapp_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('display_phone')->nullable();
            $table->string('wuzapi_user_token_hash')->unique();
            $table->text('wuzapi_user_token_encrypted');
            $table->string('webhook_url')->nullable();
            $table->enum('status', ['disconnected', 'pending', 'connected', 'error'])->default('disconnected');
            $table->json('last_session_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_integrations');
    }
};















