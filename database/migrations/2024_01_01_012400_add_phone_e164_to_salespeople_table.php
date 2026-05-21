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
        Schema::table('salespeople', function (Blueprint $table) {
            if (!Schema::hasColumn('salespeople', 'phone_e164')) {
                $table->string('phone_e164')->nullable()->after('phone');
                $table->index('phone_e164');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salespeople', function (Blueprint $table) {
            if (Schema::hasColumn('salespeople', 'phone_e164')) {
                $table->dropIndex(['phone_e164']);
                $table->dropColumn('phone_e164');
            }
        });
    }
};
