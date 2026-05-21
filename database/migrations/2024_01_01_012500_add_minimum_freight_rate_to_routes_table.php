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
            // Taxa mínima configurável para a rota
            $table->enum('min_freight_rate_type', ['percentage', 'fixed'])->nullable()->after('total_revenue');
            $table->decimal('min_freight_rate_value', 10, 2)->nullable()->after('min_freight_rate_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn(['min_freight_rate_type', 'min_freight_rate_value']);
        });
    }
};
