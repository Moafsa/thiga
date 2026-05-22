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
        Schema::table('plans', function (Blueprint $table) {
            // Split percentage para comissão do superadmin
            // Ex: 10 = 10% de comissão sobre faturas do tenant
            // 0 = sem comissão
            $table->decimal('split_percentage', 5, 2)
                ->default(0)
                ->after('price')
                ->comment('Percentage split commission for superadmin on tenant invoices');

            // Índice para buscas por split
            $table->index('split_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['split_percentage']);
            $table->dropColumn('split_percentage');
        });
    }
};
