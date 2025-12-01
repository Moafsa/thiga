<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('phone_e164', 20)->nullable()->index()->after('phone');
        });

        $normalizePhone = static function (?string $phone): ?string {
            if (!$phone) {
                return null;
            }

            $digits = preg_replace('/\D/', '', $phone);

            if (!$digits) {
                return null;
            }

            if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
                return $digits;
            }

            if (strlen($digits) >= 10 && strlen($digits) <= 11) {
                return '55' . $digits;
            }

            return $digits;
        };

        DB::table('drivers')
            ->select(['id', 'phone'])
            ->orderBy('id')
            ->chunkById(100, function ($drivers) use ($normalizePhone) {
                foreach ($drivers as $driver) {
                    $normalized = $normalizePhone($driver->phone);

                    if ($normalized) {
                        DB::table('drivers')
                            ->where('id', $driver->id)
                            ->update(['phone_e164' => $normalized]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('phone_e164');
        });
    }
};
















