<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona route_id à tabela shipments se não existir.
     * A migration 2025_10_22_190450_create_shipments_table.php verifica se a tabela existe,
     * então pode não ter criado a coluna route_id se a tabela já existia.
     */
    public function up(): void
    {
        if (Schema::hasTable('shipments') && !Schema::hasColumn('shipments', 'route_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->foreignId('route_id')->nullable()->after('tenant_id')->constrained()->onDelete('set null');
                $table->index(['route_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'route_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropForeign(['route_id']);
                $table->dropIndex(['route_id', 'status']);
                $table->dropColumn('route_id');
            });
        }
    }
};

