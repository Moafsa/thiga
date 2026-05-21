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
            // Taxa mínima configurável para a tabela de frete
            // Permite tanto percentual quanto valor fixo
            $table->enum('min_freight_rate_type', ['percentage', 'fixed'])->nullable()->after('min_freight_rate_vs_nf');
            $table->decimal('min_freight_rate_value', 10, 2)->nullable()->after('min_freight_rate_type');
            
            // Se não tiver tipo definido mas tiver min_freight_rate_vs_nf, assume percentage
            // Isso será feito na aplicação para manter compatibilidade
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freight_tables', function (Blueprint $table) {
            $table->dropColumn(['min_freight_rate_type', 'min_freight_rate_value']);
        });
    }
};
