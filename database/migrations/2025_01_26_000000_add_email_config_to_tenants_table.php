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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('email_provider')->nullable()->after('accent_color'); // postmark, mailchimp, smtp
            $table->json('email_config')->nullable()->after('email_provider'); // Configurações específicas do provedor
            $table->boolean('send_proposal_by_email')->default(true)->after('email_config');
            $table->boolean('send_proposal_by_whatsapp')->default(false)->after('send_proposal_by_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'email_provider',
                'email_config',
                'send_proposal_by_email',
                'send_proposal_by_whatsapp',
            ]);
        });
    }
};
