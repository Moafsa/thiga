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
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'phone_e164')) {
                $table->string('phone_e164')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('clients', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('tenant_id')->constrained()->onDelete('set null');
            }
            if (Schema::hasColumn('clients', 'phone_e164')) {
                $table->index('phone_e164');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'phone_e164')) {
                $table->dropIndex(['phone_e164']);
                $table->dropColumn('phone_e164');
            }
            if (Schema::hasColumn('clients', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
