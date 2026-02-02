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
        Schema::table('proposals', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->nullable()->after('description')->comment('Peso real em kg');
            $table->decimal('height', 8, 3)->nullable()->after('weight')->comment('Altura em metros');
            $table->decimal('width', 8, 3)->nullable()->after('height')->comment('Largura em metros');
            $table->decimal('length', 8, 3)->nullable()->after('width')->comment('Comprimento em metros');
            $table->decimal('cubage', 10, 3)->nullable()->after('length')->comment('Cubagem em m³');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['weight', 'height', 'width', 'length', 'cubage']);
        });
    }
};
