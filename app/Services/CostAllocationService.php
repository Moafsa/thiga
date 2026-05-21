<?php

namespace App\Services;

use App\Models\Route;
use App\Models\RouteExpense;
use App\Models\Shipment;
use App\Models\ShipmentCostAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CostAllocationService
{
    /**
     * Allocate a specific route expense to all shipments in the route.
     *
     * @param RouteExpense $expense
     * @return void
     */
    public function allocate(RouteExpense $expense): void
    {
        DB::transaction(function () use ($expense) {
            // Remove existing allocations for this expense
            $expense->allocations()->delete();

            $route = $expense->route;
            if (!$route) {
                return;
            }

            // Get all shipments in the route
            $shipments = $route->shipments;
            if ($shipments->isEmpty()) {
                return;
            }

            $totalAmount = (float) $expense->amount;
            if ($totalAmount <= 0) {
                return;
            }

            $method = $expense->allocation_method;

            // Handle direct allocation
            if ($method === 'direto') {
                if ($expense->shipment_id) {
                    $this->createAllocation(
                        $expense,
                        $expense->shipment_id,
                        $totalAmount,
                        1.0,
                        'Alocação direta ao CTe'
                    );
                } else {
                    // Fallback to equal if direct has no shipment
                    $this->allocateEqually($expense, $shipments, $totalAmount);
                }
                return;
            }

            // Route-level allocations based on proportional methods
            switch ($method) {
                case 'proporcional_valor':
                    $this->allocateProportionally($expense, $shipments, $totalAmount, 'value', 'Valor');
                    break;
                case 'proporcional_peso':
                    $this->allocateProportionally($expense, $shipments, $totalAmount, 'weight', 'Peso');
                    break;
                case 'proporcional_volume':
                    $this->allocateProportionally($expense, $shipments, $totalAmount, 'volume', 'Volume');
                    break;
                case 'igualitario':
                default:
                    $this->allocateEqually($expense, $shipments, $totalAmount);
                    break;
            }
        });
    }

    /**
     * Recalculate all allocations for a given route.
     *
     * @param Route $route
     * @return void
     */
    public function recalculate(Route $route): void
    {
        Log::info('Recalculating cost allocations for route', ['route_id' => $route->id]);
        
        $expenses = $route->routeExpenses;
        foreach ($expenses as $expense) {
            $this->allocate($expense);
        }
    }

    /**
     * Allocate equally among all shipments.
     */
    protected function allocateEqually(RouteExpense $expense, $shipments, float $totalAmount): void
    {
        $count = $shipments->count();
        $share = round($totalAmount / $count, 2);
        $remainder = round($totalAmount - ($share * $count), 2);

        $pct = round(1 / $count, 4);

        foreach ($shipments as $index => $shipment) {
            $amount = $share;
            // Add remainder to the last shipment to prevent rounding discrepancy
            if ($index === $count - 1) {
                $amount += $remainder;
            }

            $basis = sprintf(
                'Divisão igualitária entre os %d CTes da rota (R$ %.2f cada)',
                $count,
                $share
            );

            $this->createAllocation($expense, $shipment->id, $amount, $pct, $basis);
        }
    }

    /**
     * Allocate proportionally by a given shipment attribute (value, weight, volume).
     */
    protected function allocateProportionally(RouteExpense $expense, $shipments, float $totalAmount, string $attribute, string $attributeLabel): void
    {
        // Calculate sum of the attribute
        $sum = 0.0;
        foreach ($shipments as $shipment) {
            $sum += (float) ($shipment->$attribute ?? 0.0);
        }

        // If the sum is zero, fallback to equal allocation
        if ($sum <= 0.0) {
            $this->allocateEqually($expense, $shipments, $totalAmount);
            return;
        }

        $allocatedSum = 0.0;
        $count = $shipments->count();

        foreach ($shipments as $index => $shipment) {
            $val = (float) ($shipment->$attribute ?? 0.0);
            $pct = $val / $sum;
            
            // For the last one, adjust to exactly match totalAmount
            if ($index === $count - 1) {
                $amount = round($totalAmount - $allocatedSum, 2);
            } else {
                $amount = round($totalAmount * $pct, 2);
                $allocatedSum += $amount;
            }

            $basis = sprintf(
                '%s do CTe: %.2f / Total Rota: %.2f (%.2f%%)',
                $attributeLabel,
                $val,
                $sum,
                $pct * 100
            );

            $this->createAllocation($expense, $shipment->id, $amount, round($pct, 4), $basis);
        }
    }

    /**
     * Helper to create a single allocation record.
     */
    protected function createAllocation(RouteExpense $expense, int $shipmentId, float $amount, float $pct, string $basis): void
    {
        ShipmentCostAllocation::create([
            'tenant_id' => $expense->tenant_id,
            'route_expense_id' => $expense->id,
            'shipment_id' => $shipmentId,
            'route_id' => $expense->route_id,
            'allocated_amount' => $amount,
            'allocation_pct' => $pct,
            'allocation_basis' => $basis,
        ]);
    }
}
