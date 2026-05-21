<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Shipment;
use App\Models\ShipmentCostAllocation;

class CostSummaryService
{
    /**
     * Get detailed cost summary and margin for a shipment.
     *
     * @param Shipment $shipment
     * @return array
     */
    public function summaryForShipment(Shipment $shipment): array
    {
        $revenue = (float) ($shipment->freight_value ?? 0.00);
        
        // Retrieve all cost allocations for this shipment
        $allocations = ShipmentCostAllocation::with('routeExpense')
            ->where('shipment_id', $shipment->id)
            ->get();

        $totalCosts = 0.00;
        $byType = [];
        $items = [];

        foreach ($allocations as $alloc) {
            $expense = $alloc->routeExpense;
            if (!$expense) {
                continue;
            }

            $allocatedAmount = (float) $alloc->allocated_amount;
            $totalCosts += $allocatedAmount;

            $type = $expense->cost_type;
            if (!isset($byType[$type])) {
                $byType[$type] = 0.00;
            }
            $byType[$type] += $allocatedAmount;

            $items[] = [
                'id' => $expense->id,
                'allocation_id' => $alloc->id,
                'cost_type' => $type,
                'description' => $expense->description,
                'allocated_amount' => $allocatedAmount,
                'allocation_basis' => $alloc->allocation_basis,
                'allocation_pct' => (float) $alloc->allocation_pct,
                'operator_type' => $expense->operator_type,
                'third_party_name' => $expense->third_party_name,
                'leg' => $expense->leg,
                'route_id' => $expense->route_id,
            ];
        }

        $margin = $revenue - $totalCosts;
        $marginPct = $revenue > 0 ? ($margin / $revenue) * 100 : 0.00;

        return [
            'revenue' => $revenue,
            'costs' => [
                'total' => $totalCosts,
                'by_type' => $byType,
                'items' => $items,
            ],
            'margin' => $margin,
            'margin_pct' => $marginPct,
        ];
    }

    /**
     * Get profitability summary for a route/manifest.
     *
     * @param Route $route
     * @return array
     */
    public function summaryForRoute(Route $route): array
    {
        $shipments = $route->shipments;
        
        $totalRevenue = 0.00;
        $shipmentSummaries = [];

        // Pre-calculate individual shipment summaries
        foreach ($shipments as $shipment) {
            $shipmentSummary = $this->summaryForShipment($shipment);
            $totalRevenue += $shipmentSummary['revenue'];

            $shipmentSummaries[] = [
                'shipment_id' => $shipment->id,
                'title' => $shipment->title,
                'cte_number' => $shipment->cte_number,
                'invoice_number' => $shipment->invoice_number,
                'revenue' => $shipmentSummary['revenue'],
                'allocated_cost' => $shipmentSummary['costs']['total'],
                'margin' => $shipmentSummary['margin'],
                'margin_pct' => $shipmentSummary['margin_pct'],
            ];
        }

        // Sum of all route expenses
        $totalCosts = (float) $route->routeExpenses()->sum('amount');
        $totalMargin = $totalRevenue - $totalCosts;
        $totalMarginPct = $totalRevenue > 0 ? ($totalMargin / $totalRevenue) * 100 : 0.00;

        return [
            'route_id' => $route->id,
            'route_name' => $route->name ?? 'Manifesto #' . $route->id,
            'total_revenue' => $totalRevenue,
            'total_costs' => $totalCosts,
            'total_margin' => $totalMargin,
            'total_margin_pct' => $totalMarginPct,
            'shipments' => $shipmentSummaries,
        ];
    }
}
