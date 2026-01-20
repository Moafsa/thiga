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
        Schema::table('routes', function (Blueprint $table) {
            // Dias da semana em que a taxa mínima da rota será aplicada
            // JSON array com dias: [0=domingo, 1=segunda, 2=terça, 3=quarta, 4=quinta, 5=sexta, 6=sábado]
            // Se null ou vazio, aplica em todos os dias
            $table->json('min_freight_rate_days')->nullable()->after('min_freight_rate_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('min_freight_rate_days');
        });
    }
};
