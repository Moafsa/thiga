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
            $table->boolean('visible_to_clients')->default(false)->after('is_default')->comment('Se a tabela pode aparecer no dashboard do cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freight_tables', function (Blueprint $table) {
            $table->dropColumn('visible_to_clients');
        });
    }
};
