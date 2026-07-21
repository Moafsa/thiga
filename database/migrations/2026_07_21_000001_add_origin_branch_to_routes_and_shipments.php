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
        if (Schema::hasTable('routes') && !Schema::hasColumn('routes', 'origin_branch')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->string('origin_branch')->nullable()->after('destination_city');
            });
        }

        if (Schema::hasTable('shipments') && !Schema::hasColumn('shipments', 'origin_branch')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->string('origin_branch')->nullable()->after('destination_city');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('routes') && Schema::hasColumn('routes', 'origin_branch')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->dropColumn('origin_branch');
            });
        }

        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'origin_branch')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropColumn('origin_branch');
            });
        }
    }
};
