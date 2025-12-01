<?php

namespace App\Services;

use App\Models\FreightTable;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class FreightCalculationService
{
    /**
     * Calculate freight based on freight table
     *
     * @param Tenant $tenant
     * @param string $destination Destination name or CEP
     * @param float $weight Weight in kg
     * @param float $cubage Volume in m³
     * @param float $invoiceValue Invoice value (NF)
     * @param array $options Additional options (tde_markets, tde_supermarkets_cd, pallets, is_weekend, etc)
     * @return array Calculation result with breakdown
     */
    public function calculate(
        Tenant $tenant,
        string $destination,
        float $weight,
        float $cubage = 0,
        float $invoiceValue = 0,
        array $options = []
    ): array {
        // Find freight table for tenant and destination
        $freightTable = $this->findFreightTable($tenant, $destination);

        if (!$freightTable) {
            throw new \Exception("Freight table not found for destination: {$destination}");
        }

        // Calculate chargeable weight (max between real weight and volumetric weight)
        $volumetricWeight = $cubage * ($freightTable->cubage_factor ?? 300);
        $chargeableWeight = max($weight, $volumetricWeight);

        // Calculate base freight (weight)
        $freightWeight = $freightTable->getWeightValue($chargeableWeight);

        // Calculate additional taxes
        $adValorem = $invoiceValue * ($freightTable->ad_valorem_rate ?? 0.0040);
        $gris = max(
            $invoiceValue * ($freightTable->gris_rate ?? 0.0030),
            $freightTable->gris_minimum ?? 8.70
        );
        $toll = ($chargeableWeight / 100) * ($freightTable->toll_per_100kg ?? 12.95);

        // Calculate subtotal
        $subtotal = $freightWeight + $adValorem + $gris + $toll;

        // Apply additional services
        $additionalServices = 0;
        $additionalBreakdown = [];

        if (!empty($options['tde_markets']) && $freightTable->tde_markets) {
            $additionalServices += $freightTable->tde_markets;
            $additionalBreakdown[] = [
                'name' => 'TDE Mercados',
                'value' => $freightTable->tde_markets
            ];
        }

        if (!empty($options['tde_supermarkets_cd']) && $freightTable->tde_supermarkets_cd) {
            $additionalServices += $freightTable->tde_supermarkets_cd;
            $additionalBreakdown[] = [
                'name' => 'TDE CD Supermercados',
                'value' => $freightTable->tde_supermarkets_cd
            ];
        }

        if (!empty($options['pallets']) && $freightTable->palletization) {
            $pallets = (int)($options['pallets'] ?? 0);
            $palletsCost = $pallets * ($freightTable->palletization ?? 0);
            $additionalServices += $palletsCost;
            $additionalBreakdown[] = [
                'name' => "Paletização ({$pallets} pallets)",
                'value' => $palletsCost
            ];
        }

        if (!empty($options['unloading']) && $freightTable->unloading_tax) {
            $additionalServices += $freightTable->unloading_tax;
            $additionalBreakdown[] = [
                'name' => 'Taxa de Descarga',
                'value' => $freightTable->unloading_tax
            ];
        }

        // Apply weekend/holiday rate if applicable
        $weekendRate = 0;
        if (!empty($options['is_weekend_or_holiday'])) {
            $weekendRate = $subtotal * ($freightTable->weekend_holiday_rate ?? 0.30);
            $subtotal += $weekendRate;
            $additionalBreakdown[] = [
                'name' => 'Taxa Fim de Semana/Feriado (30%)',
                'value' => $weekendRate
            ];
        }

        // Apply redelivery or return rate if applicable
        if (!empty($options['is_redelivery'])) {
            $redeliveryRate = $subtotal * ($freightTable->redelivery_rate ?? 0.50);
            $subtotal += $redeliveryRate;
            $additionalBreakdown[] = [
                'name' => 'Reentrega (50%)',
                'value' => $redeliveryRate
            ];
        } elseif (!empty($options['is_return'])) {
            $returnRate = $subtotal * ($freightTable->return_rate ?? 1.00);
            $subtotal += $returnRate;
            $additionalBreakdown[] = [
                'name' => 'Devolução (100%)',
                'value' => $returnRate
            ];
        }

        // Add additional services
        $subtotal += $additionalServices;

        // Apply minimum freight rule (1% of invoice value)
        $minFreight = $invoiceValue * ($freightTable->min_freight_rate_vs_nf ?? 0.01);
        $appliedMinimum = false;
        
        if ($subtotal < $minFreight && $invoiceValue > 0) {
            $subtotal = $minFreight;
            $appliedMinimum = true;
        }

        // Calculate weight breakdown
        $weightBreakdown = $this->getWeightBreakdown($freightTable, $chargeableWeight);

        return [
            'total' => round($subtotal, 2),
            'breakdown' => [
                'chargeable_weight' => round($chargeableWeight, 2),
                'real_weight' => round($weight, 2),
                'volumetric_weight' => round($volumetricWeight, 2),
                'freight_weight' => round($freightWeight, 2),
                'weight_breakdown' => $weightBreakdown,
                'ad_valorem' => round($adValorem, 2),
                'gris' => round($gris, 2),
                'toll' => round($toll, 2),
                'additional_services' => $additionalBreakdown,
                'minimum_applied' => $appliedMinimum,
                'minimum_value' => $appliedMinimum ? round($minFreight, 2) : null,
            ],
            'freight_table' => [
                'id' => $freightTable->id,
                'name' => $freightTable->name,
                'destination' => $freightTable->destination_name,
            ],
        ];
    }

    /**
     * Find freight table for tenant and destination
     */
    protected function findFreightTable(Tenant $tenant, string $destination): ?FreightTable
    {
        // Try to find by destination name
        $table = FreightTable::where('tenant_id', $tenant->id)
            ->active()
            ->where('destination_name', 'like', "%{$destination}%")
            ->first();

        if ($table) {
            return $table;
        }

        // Try to find by CEP if destination looks like a CEP
        if (preg_match('/^\d{5}-?\d{3}$/', $destination)) {
            $table = FreightTable::where('tenant_id', $tenant->id)
                ->active()
                ->where('destination_type', 'cep_range')
                ->get()
                ->first(function ($table) use ($destination) {
                    return $table->isCepInRange($destination);
                });

            if ($table) {
                return $table;
            }
        }

        // Return default table if exists
        return FreightTable::where('tenant_id', $tenant->id)
            ->active()
            ->default()
            ->first();
    }

    /**
     * Get weight breakdown for display
     */
    protected function getWeightBreakdown(FreightTable $table, float $weight): array
    {
        if ($weight <= 30) {
            return [
                'range' => '0-30kg',
                'base_value' => $table->weight_0_30,
                'excess' => null,
                'ctrc' => null,
            ];
        } elseif ($weight <= 50) {
            return [
                'range' => '31-50kg',
                'base_value' => $table->weight_31_50,
                'excess' => null,
                'ctrc' => null,
            ];
        } elseif ($weight <= 70) {
            return [
                'range' => '51-70kg',
                'base_value' => $table->weight_51_70,
                'excess' => null,
                'ctrc' => null,
            ];
        } elseif ($weight <= 100) {
            return [
                'range' => '71-100kg',
                'base_value' => $table->weight_71_100,
                'excess' => null,
                'ctrc' => null,
            ];
        } else {
            $base = $table->weight_71_100 ?? 0;
            $excessWeight = $weight - 100;
            $excessCost = $excessWeight * ($table->weight_over_100_rate ?? 0);
            $ctrc = $table->ctrc_tax ?? 0;

            return [
                'range' => 'Acima de 100kg',
                'base_value' => $base,
                'excess' => [
                    'weight' => round($excessWeight, 2),
                    'rate' => $table->weight_over_100_rate,
                    'cost' => round($excessCost, 2),
                ],
                'ctrc' => $ctrc,
            ];
        }
    }
}





















