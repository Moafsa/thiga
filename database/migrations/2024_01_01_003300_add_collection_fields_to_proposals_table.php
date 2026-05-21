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
            // Campos para solicitação de coleta
            $table->boolean('collection_requested')->default(false)->after('status');
            $table->timestamp('collection_requested_at')->nullable()->after('collection_requested');
            
            // Endereço de origem (coleta)
            $table->string('origin_address')->nullable()->after('collection_requested_at');
            $table->string('origin_city')->nullable()->after('origin_address');
            $table->string('origin_state', 2)->nullable()->after('origin_city');
            $table->string('origin_zip_code', 10)->nullable()->after('origin_state');
            $table->decimal('origin_latitude', 10, 8)->nullable()->after('origin_zip_code');
            $table->decimal('origin_longitude', 11, 8)->nullable()->after('origin_latitude');
            
            // Endereço de destino (entrega)
            $table->string('destination_address')->nullable()->after('origin_longitude');
            $table->string('destination_city')->nullable()->after('destination_address');
            $table->string('destination_state', 2)->nullable()->after('destination_city');
            $table->string('destination_zip_code', 10)->nullable()->after('destination_state');
            $table->decimal('destination_latitude', 10, 8)->nullable()->after('destination_zip_code');
            $table->decimal('destination_longitude', 11, 8)->nullable()->after('destination_latitude');
            
            // Dados adicionais do formulário HTML
            $table->string('client_name')->nullable()->after('destination_longitude');
            $table->string('client_whatsapp')->nullable()->after('client_name');
            $table->string('client_email')->nullable()->after('client_whatsapp');
            $table->string('destination_name')->nullable()->after('client_email'); // Nome do destino (ex: BELO HORIZONTE - MG)
            
            $table->index(['collection_requested', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropIndex(['collection_requested', 'status']);
            $table->dropColumn([
                'collection_requested',
                'collection_requested_at',
                'origin_address',
                'origin_city',
                'origin_state',
                'origin_zip_code',
                'origin_latitude',
                'origin_longitude',
                'destination_address',
                'destination_city',
                'destination_state',
                'destination_zip_code',
                'destination_latitude',
                'destination_longitude',
                'client_name',
                'client_whatsapp',
                'client_email',
                'destination_name',
            ]);
        });
    }
};
