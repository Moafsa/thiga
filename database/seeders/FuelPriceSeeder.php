<?php

namespace Database\Seeders;

use App\Models\FuelPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuelPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing prices
        DB::table('fuel_prices')->truncate();

        // National average prices (approximate Brazilian prices as of December 2024)
        // These should be updated regularly or fetched from an API
        
        $prices = [
            [
                'fuel_type' => 'diesel',
                'price_per_liter' => 5.50,
                'effective_date' => now()->startOfDay(),
                'expires_at' => null,
                'region' => null, // National
                'is_active' => true,
                'notes' => 'Diesel S10 - Preço médio nacional',
            ],
            [
                'fuel_type' => 'gasoline',
                'price_per_liter' => 5.80,
                'effective_date' => now()->startOfDay(),
                'expires_at' => null,
                'region' => null, // National
                'is_active' => true,
                'notes' => 'Gasolina comum - Preço médio nacional',
            ],
            [
                'fuel_type' => 'ethanol',
                'price_per_liter' => 3.90,
                'effective_date' => now()->startOfDay(),
                'expires_at' => null,
                'region' => null, // National
                'is_active' => true,
                'notes' => 'Etanol - Preço médio nacional',
            ],
            [
                'fuel_type' => 'cng',
                'price_per_liter' => 3.50,
                'effective_date' => now()->startOfDay(),
                'expires_at' => null,
                'region' => null, // National
                'is_active' => true,
                'notes' => 'GNV - Preço médio nacional',
            ],
        ];

        foreach ($prices as $price) {
            FuelPrice::create($price);
        }

        $this->command->info('Fuel prices seeded successfully!');
        $this->command->info('Note: Update these prices regularly or integrate with a fuel price API.');
    }
}















