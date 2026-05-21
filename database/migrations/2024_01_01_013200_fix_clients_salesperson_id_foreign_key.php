<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Dropar a foreign key antiga que aponta para 'users'
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['salesperson_id']);
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            // Criar a foreign key correta apontando para 'salespeople'
            $table->foreign('salesperson_id')
                ->references('id')
                ->on('salespeople')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Dropar a foreign key correta
            $table->dropForeign(['salesperson_id']);
        });

        Schema::table('clients', function (Blueprint $table) {
            // Restaurar a foreign key antiga (apontando para 'users')
            $table->foreign('salesperson_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
