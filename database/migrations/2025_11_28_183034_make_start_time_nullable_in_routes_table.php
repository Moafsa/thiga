<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes start_time nullable in routes table since it's optional in the form.
     */
    public function up(): void
    {
        if (Schema::hasTable('routes') && Schema::hasColumn('routes', 'start_time')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->time('start_time')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('routes') && Schema::hasColumn('routes', 'start_time')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->time('start_time')->nullable(false)->change();
            });
        }
    }
};
