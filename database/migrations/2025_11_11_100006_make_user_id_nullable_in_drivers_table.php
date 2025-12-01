<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Torna user_id nullable na tabela drivers.
     * Um motorista pode existir sem ter um usuário no sistema inicialmente.
     * O user pode ser criado depois quando necessário para login.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Remove a constraint NOT NULL e torna nullable
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não podemos reverter facilmente porque pode haver drivers sem user_id
        // Se necessário, criar uma migration específica para isso
    }
};

