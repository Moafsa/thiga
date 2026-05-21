<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campo ownership_type para diferenciar entre veículos da frota e terceiros.
     * - 'fleet' (frota): pode ter manutenções e despesas vinculadas
     * - 'third_party' (terceiro): não pode ter manutenções nem despesas
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->enum('ownership_type', ['fleet', 'third_party'])->default('fleet')->after('is_active');
            $table->index(['tenant_id', 'ownership_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'ownership_type']);
            $table->dropColumn('ownership_type');
        });
    }
};

