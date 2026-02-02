<?php

namespace Tests\Feature;

use App\Models\FreightTable;
use App\Models\Tenant;
use App\Services\FreightCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FreightCalculationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed or create necessary data
        $plan = \App\Models\Plan::create([
            'name' => 'Basic Plan',
            'price' => 100.00,
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test',
            'cnpj' => '12345678000199',
            'plan_id' => $plan->id,
        ]);

        // Create a freight table
        $this->freightTable = FreightTable::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tabela SP',
            'destination_name' => 'São Paulo',
            'destination_state' => 'SP',
            'weight_0_30' => 50.00,
            'weight_31_50' => 70.00,
            'weight_51_70' => 90.00,
            'weight_71_100' => 120.00,
            'weight_over_100_rate' => 1.50,
            'ctrc_tax' => 5.00,
            'ad_valorem_rate' => 0.01, // 1%
            'gris_rate' => 0.005, // 0.5%
            'gris_minimum' => 10.00,
            'toll_per_100kg' => 15.00,
            'cubage_factor' => 300,
            'tde_markets' => 25.00,
            'tde_supermarkets_cd' => 50.00,
            'palletization' => 30.00, // Por pallet
            'unloading_tax' => 40.00,
            'weekend_holiday_rate' => 0.20, // 20%
        ]);

        $this->service = app(FreightCalculationService::class);
    }

    public function test_calculate_basic_freight()
    {
        $weight = 20.0; // 0-30 range -> 50.00
        $invoiceValue = 1000.00;

        $result = $this->service->calculate(
            $this->tenant,
            'São Paulo',
            $weight,
            0.1, // volume
            $invoiceValue
        );

        // Base: 50.00
        // Ad Valorem: 1000 * 0.01 = 10.00
        // Gris: max(1000 * 0.005 = 5.00, 10.00) = 10.00
        // Pedágio: (Max(20, 0.1*300=30) / 100) * 15.00 = 0.3 * 15.00 = 4.50
        // Taxa CTRC: Não entra na conta do calculate() por enquanto, não vi no código original somando, 
        // mas vamos checar o código original. 
        // O código original FreightCalculationService.php linha 59: $subtotal = $freightWeight + $tda + $adValorem + $gris + $toll;
        // Não soma CTRC no total, apenas retorna no breakdown se > 100kg? 
        // Ah, getWeightBreakdown retorna ctrc, mas não soma no subtotal geral explicitamente na linha 59.

        // Expected total around: 50 + 10 + 10 + 4.5 = 74.50

        $this->assertGreaterThan(70, $result['total']);
        $this->assertEquals(50.00, $result['breakdown']['freight_weight']);
    }

    public function test_calculate_with_tde_markets()
    {
        $result = $this->service->calculate(
            $this->tenant,
            'São Paulo',
            20.0,
            0.1,
            1000.00,
            ['tde_markets' => true]
        );

        // Previous total + 25.00
        // Verify breakdown contains TDE Mercados
        $hasTde = false;
        foreach ($result['breakdown']['additional_services'] as $service) {
            if ($service['name'] === 'TDE Mercados') {
                $hasTde = true;
                $this->assertEquals(25.00, $service['value']);
            }
        }
        $this->assertTrue($hasTde);
    }

    public function test_calculate_with_pallets()
    {
        $pallets = 3;
        $result = $this->service->calculate(
            $this->tenant,
            'São Paulo',
            20.0,
            0.1,
            1000.00,
            ['pallets' => $pallets]
        );

        // Pallets cost: 3 * 30.00 = 90.00

        $hasPallets = false;
        foreach ($result['breakdown']['additional_services'] as $service) {
            if (str_contains($service['name'], 'Paletização')) {
                $hasPallets = true;
                $this->assertEquals(90.00, $service['value']);
            }
        }
        $this->assertTrue($hasPallets);
    }

    public function test_calculate_weekend_rate()
    {
        // Base subtotal (approx 74.50)
        // Weekend rate 20%

        $result = $this->service->calculate(
            $this->tenant,
            'São Paulo',
            20.0,
            0.1,
            1000.00,
            ['is_weekend_or_holiday' => true]
        );

        $hasWeekend = false;
        foreach ($result['breakdown']['additional_services'] as $service) {
            if (str_contains($service['name'], 'Fim de Semana')) {
                $hasWeekend = true;
                // Value should be > 0
                $this->assertGreaterThan(0, $service['value']);
            }
        }
        $this->assertTrue($hasWeekend);
    }
}
