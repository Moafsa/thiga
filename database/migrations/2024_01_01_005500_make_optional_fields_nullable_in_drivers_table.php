<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Torna nullable os campos opcionais na tabela drivers.
     * Permite cadastrar motoristas com informações parciais.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // For PostgreSQL, we need to use raw SQL to alter the columns
        $columns = ['email', 'phone', 'document', 'cnh_number', 'cnh_category', 'cnh_expiry_date'];
        
        foreach ($columns as $column) {
            DB::statement("ALTER TABLE drivers ALTER COLUMN {$column} DROP NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Set NOT NULL constraint back
        $columns = ['email', 'phone', 'document', 'cnh_number', 'cnh_category', 'cnh_expiry_date'];
        
        foreach ($columns as $column) {
            DB::statement("ALTER TABLE drivers ALTER COLUMN {$column} SET NOT NULL");
        }
    }
};

