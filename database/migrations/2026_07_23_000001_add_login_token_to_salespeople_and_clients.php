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
        if (Schema::hasTable('salespeople')) {
            Schema::table('salespeople', function (Blueprint $table) {
                if (!Schema::hasColumn('salespeople', 'login_token')) {
                    $table->string('login_token', 64)->nullable()->unique()->after('user_id');
                }
                if (!Schema::hasColumn('salespeople', 'temp_password')) {
                    $table->string('temp_password', 255)->nullable()->after('login_token');
                }
            });
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (!Schema::hasColumn('clients', 'login_token')) {
                    $table->string('login_token', 64)->nullable()->unique()->after('user_id');
                }
                if (!Schema::hasColumn('clients', 'temp_password')) {
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
        if (Schema::hasTable('salespeople')) {
            Schema::table('salespeople', function (Blueprint $table) {
                if (Schema::hasColumn('salespeople', 'login_token')) {
                    $table->dropColumn('login_token');
                }
                if (Schema::hasColumn('salespeople', 'temp_password')) {
                    $table->dropColumn('temp_password');
                }
            });
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (Schema::hasColumn('clients', 'login_token')) {
                    $table->dropColumn('login_token');
                }
                if (Schema::hasColumn('clients', 'temp_password')) {
                    $table->dropColumn('temp_password');
                }
            });
        }
    }
};
