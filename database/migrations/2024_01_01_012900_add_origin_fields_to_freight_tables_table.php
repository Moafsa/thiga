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
        Schema::table('freight_tables', function (Blueprint $table) {
            $table->string('origin_name')->nullable()->after('destination_state');
            $table->string('origin_state', 2)->nullable()->after('origin_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freight_tables', function (Blueprint $table) {
            $table->dropColumn(['origin_name', 'origin_state']);
        });
    }
};
