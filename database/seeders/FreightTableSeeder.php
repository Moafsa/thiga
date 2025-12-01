<?php

namespace Database\Seeders;

use App\Models\FreightTable;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FreightTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found. Please create a tenant first.');
            return;
        }

        // Data from pracas.html
        $freightData = [
            [
                'name' => 'BELO HORIZONTE - MG',
                'destination_name' => 'BELO HORIZONTE - MG',
                'destination_state' => 'MG',
                'weight_0_30' => 78.30,
                'weight_31_50' => 87.08,
                'weight_51_70' => 103.75,
                'weight_71_100' => 118.60,
                'weight_over_100_rate' => 0.86,
                'ctrc_tax' => 60.45,
            ],
            [
                'name' => 'GRANDE BH - MG',
                'destination_name' => 'GRANDE BH - MG',
                'destination_state' => 'MG',
                'weight_0_30' => 83.46,
                'weight_31_50' => 92.78,
                'weight_51_70' => 110.86,
                'weight_71_100' => 122.50,
                'weight_over_100_rate' => 0.90,
                'ctrc_tax' => 60.45,
            ],
            [
                'name' => 'SETE LAGOAS - MG',
                'destination_name' => 'SETE LAGOAS - MG',
                'destination_state' => 'MG',
                'weight_0_30' => 86.05,
                'weight_31_50' => 95.35,
                'weight_51_70' => 114.75,
                'weight_71_100' => 126.35,
                'weight_over_100_rate' => 1.00,
                'ctrc_tax' => 60.45,
            ],
            [
                'name' => 'CENTRO-OESTE MG',
                'destination_name' => 'DIVINÓPOLIS – ITAÚNA - PARÁ MINAS - MG',
                'destination_state' => 'MG',
                'weight_0_30' => 90.20,
                'weight_31_50' => 102.20,
                'weight_51_70' => 117.30,
                'weight_71_100' => 131.50,
                'weight_over_100_rate' => 1.50,
                'ctrc_tax' => 60.45,
            ],
            [
                'name' => 'JUIZ DE FORA - MG',
                'destination_name' => 'JUIZ DE FORA - MG',
                'destination_state' => 'MG',
                'weight_0_30' => 96.65,
                'weight_31_50' => 105.70,
                'weight_51_70' => 118.60,
                'weight_71_100' => 134.10,
                'weight_over_100_rate' => 1.60,
                'ctrc_tax' => 60.45,
            ],
            [
                'name' => 'DEMAIS CIDADES - MG',
                'destination_name' => 'DEMAIS CIDADES - MG',
                'destination_state' => 'MG',
                'weight_0_30' => 99.90,
                'weight_31_50' => 108.30,
                'weight_51_70' => 123.80,
                'weight_71_100' => 136.70,
                'weight_over_100_rate' => 1.85,
                'ctrc_tax' => 72.00,
            ],
            [
                'name' => 'NORTE DE MINAS - MG',
                'destination_name' => 'NORTE DE MINAS - MG',
                'destination_state' => 'MG',
                'weight_0_30' => 107.60,
                'weight_31_50' => 115.85,
                'weight_51_70' => 126.40,
                'weight_71_100' => 149.30,
                'weight_over_100_rate' => 1.95,
                'ctrc_tax' => 72.00,
            ],
        ];

        // Default calculation settings from pracas.html
        $defaultSettings = [
            'ad_valorem_rate' => 0.0040, // 0,40%
            'gris_rate' => 0.0030, // 0,30%
            'gris_minimum' => 8.70,
            'toll_per_100kg' => 12.95,
            'cubage_factor' => 300, // kg/m³
            'min_freight_rate_vs_nf' => 0.01, // 1%
            'weekend_holiday_rate' => 0.30, // 30%
            'redelivery_rate' => 0.50, // 50%
            'return_rate' => 1.00, // 100%
        ];

        foreach ($tenants as $tenant) {
            $isFirst = true;

            foreach ($freightData as $data) {
                // Check if freight table already exists for this tenant
                $exists = FreightTable::where('tenant_id', $tenant->id)
                    ->where('destination_name', $data['destination_name'])
                    ->exists();

                if (!$exists) {
                    FreightTable::create(array_merge($data, [
                        'tenant_id' => $tenant->id,
                        'destination_type' => 'city',
                        'description' => 'Tabela de frete padrão para ' . $data['destination_name'],
                        'is_active' => true,
                        'is_default' => $isFirst, // First table is default
                    ], $defaultSettings));

                    $isFirst = false;

                    $this->command->info("Created freight table for {$data['destination_name']} (Tenant: {$tenant->domain})");
                } else {
                    $this->command->warn("Freight table for {$data['destination_name']} already exists (Tenant: {$tenant->domain})");
                }
            }
        }
    }
}
