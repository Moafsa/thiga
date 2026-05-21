<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_login_codes', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone_e164');
        });

        Schema::table('client_login_codes', function (Blueprint $table) {
            $table->string('phone_e164')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('client_login_codes', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('client_login_codes', function (Blueprint $table) {
            $table->string('phone_e164')->nullable(false)->change();
        });
    }
};
