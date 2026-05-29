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
        Schema::create('route_capacity_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_space_booking_id')->nullable()->constrained('route_space_bookings')->onDelete('set null');
            $table->string('entry_type'); // reserve, confirm, cancel, release
            $table->decimal('weight_delta', 10, 2);
            $table->decimal('volume_delta', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_capacity_ledger_entries');
    }
};
