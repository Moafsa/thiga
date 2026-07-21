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
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (!Schema::hasColumn('drivers', 'login_token')) {
                    $table->string('login_token', 64)->nullable()->unique()->after('user_id');
                }
                if (!Schema::hasColumn('drivers', 'temp_password')) {
                    $table->string('temp_password', 255)->nullable()->after('login_token');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (Schema::hasColumn('drivers', 'login_token')) {
                    $table->dropColumn('login_token');
                }
                if (Schema::hasColumn('drivers', 'temp_password')) {
                    $table->dropColumn('temp_password');
                }
            });
        }
    }
};
