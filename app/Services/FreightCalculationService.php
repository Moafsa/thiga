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
     * @param array $options Additional options (tde_markets, tde_supermarkets_cd, pallets, is_weekend, route_id, client_id, etc)
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

        // Calculate TDA (Taxa de Dificuldade de Acesso) - aplicada sobre o frete peso
        $tda = 0;
        if ($freightTable->tda_rate && $freightTable->tda_rate > 0) {
            $tda = $freightWeight * ($freightTable->tda_rate ?? 0);
        }

        // Calculate additional taxes
        $adValorem = $invoiceValue * ($freightTable->ad_valorem_rate ?? 0.0040);
        $gris = max(
            $invoiceValue * ($freightTable->gris_rate ?? 0.0030),
            $freightTable->gris_minimum ?? 8.70
        );
        $toll = ($chargeableWeight / 100) * ($freightTable->toll_per_100kg ?? 12.95);

        // Calculate subtotal
        $subtotal = $freightWeight + $tda + $adValorem + $gris + $toll;

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

        // Apply weekend/holiday rate if applicable (manual checkbox or date in configured ranges)
        $weekendRate = 0;
        $applyWeekend = !empty($options['is_weekend_or_holiday']);
        if (!$applyWeekend && ($freightTable->getWeekendHolidayDates() !== [])) {
            foreach (['pickup_date', 'delivery_date'] as $key) {
                $date = $options[$key] ?? null;
                if ($date && $freightTable->isDateInWeekendHolidayRanges($date)) {
                    $applyWeekend = true;
                    break;
                }
            }
        }
        if ($applyWeekend) {
            $rate = $freightTable->weekend_holiday_rate ?? 0.30;
            $weekendRate = $subtotal * $rate;
            $subtotal += $weekendRate;
            $pct = round($rate * 100);
            $additionalBreakdown[] = [
                'name' => "Taxa Fim de Semana/Feriado ({$pct}%)",
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

        // Calculate minimum freight (priority: route > freight table > default)
        $minFreight = $this->calculateMinimumFreight(
            $subtotal,
            $invoiceValue,
            $freightTable,
            $options['route_id'] ?? null,
            $options['client_id'] ?? null
        );
        $appliedMinimum = false;
        
        if ($subtotal < $minFreight['value'] && $minFreight['value'] > 0) {
            $subtotal = $minFreight['value'];
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
                'tda' => round($tda, 2),
                'weight_breakdown' => $weightBreakdown,
                'ad_valorem' => round($adValorem, 2),
                'gris' => round($gris, 2),
                'toll' => round($toll, 2),
                'additional_services' => $additionalBreakdown,
                'minimum_applied' => $appliedMinimum,
                'minimum_value' => $appliedMinimum ? round($minFreight['value'], 2) : null,
                'minimum_source' => $appliedMinimum ? $minFreight['source'] : null,
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

    /**
     * Calculate minimum freight based on priority:
     * 1. Route minimum rate (if route_id provided)
     * 2. Freight table minimum rate
     * 3. Default minimum rate (percentage of invoice value)
     *
     * @param float $subtotal Current subtotal
     * @param float $invoiceValue Invoice value
     * @param FreightTable $freightTable Freight table being used
     * @param int|null $routeId Route ID (optional)
     * @param int|null $clientId Client ID (optional, for future use)
     * @return array ['value' => float, 'source' => string]
     */
    protected function calculateMinimumFreight(
        float $subtotal,
        float $invoiceValue,
        FreightTable $freightTable,
        ?int $routeId = null,
        ?int $clientId = null
    ): array {
        // Priority 1: Route minimum rate
        if ($routeId) {
            $route = \App\Models\Route::find($routeId);
            if ($route && $route->min_freight_rate_type && $route->min_freight_rate_value) {
                // Verifica se deve aplicar taxa mínima da rota baseado nos dias da semana
                $shouldApplyRouteMinimum = $this->shouldApplyRouteMinimum($route);
                
                if ($shouldApplyRouteMinimum) {
                    $minValue = $this->calculateMinimumByType(
                        $route->min_freight_rate_type,
                        $route->min_freight_rate_value,
                        $invoiceValue
                    );
                    
                    if ($minValue > 0) {
                        return [
                            'value' => $minValue,
                            'source' => 'route'
                        ];
                    }
                }
            }
        }

        // Priority 2: Freight table minimum rate
        if ($freightTable->min_freight_rate_type && $freightTable->min_freight_rate_value) {
            $minValue = $this->calculateMinimumByType(
                $freightTable->min_freight_rate_type,
                $freightTable->min_freight_rate_value,
                $invoiceValue
            );
            
            if ($minValue > 0) {
                return [
                    'value' => $minValue,
                    'source' => 'freight_table'
                ];
            }
        }

        // Priority 3: Default minimum (percentage of invoice value)
        $minValue = $invoiceValue * ($freightTable->min_freight_rate_vs_nf ?? 0.01);
        
        return [
            'value' => $minValue,
            'source' => 'default'
        ];
    }

    /**
     * Calculate minimum freight value based on type (percentage or fixed)
     *
     * @param string $type 'percentage' or 'fixed'
     * @param float $value The rate value (percentage as decimal or fixed amount)
     * @param float $invoiceValue Invoice value (for percentage calculation)
     * @return float Minimum freight value
     */
    protected function calculateMinimumByType(string $type, float $value, float $invoiceValue): float
    {
        if ($type === 'percentage') {
            // Value is already a percentage (e.g., 0.01 for 1%)
            // If value > 1, assume it's in percentage form (e.g., 1.00 for 1%)
            if ($value > 1) {
                $value = $value / 100;
            }
            return $invoiceValue * $value;
        } elseif ($type === 'fixed') {
            // Value is a fixed amount in R$
            return $value;
        }

        return 0;
    }

    /**
     * Check if route minimum rate should be applied based on day of week
     *
     * @param \App\Models\Route $route
     * @return bool
     */
    protected function shouldApplyRouteMinimum(\App\Models\Route $route): bool
    {
        // Se não tem dias específicos configurados, aplica sempre
        if (empty($route->min_freight_rate_days) || !is_array($route->min_freight_rate_days)) {
            return true;
        }

        // Se array está vazio, aplica sempre
        if (count($route->min_freight_rate_days) === 0) {
            return true;
        }

        // Obtém o dia da semana da data agendada da rota (0 = domingo, 6 = sábado)
        // Se não tiver data agendada, usa o dia atual
        $routeDate = $route->scheduled_date ?? now();
        if ($routeDate instanceof \Carbon\Carbon || $routeDate instanceof \DateTime) {
            $dayOfWeek = (int) $routeDate->format('w');
        } else {
            $dayOfWeek = (int) date('w', strtotime($routeDate));
        }

        // Verifica se o dia da rota está na lista de dias permitidos
        return in_array($dayOfWeek, $route->min_freight_rate_days, true);
    }
}





















