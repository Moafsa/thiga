<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\FuelPrice;
use Illuminate\Support\Facades\Log;

class FuelCostService
{
    /**
     * Calculate fuel cost for a route
     * 
     * @param float $distanceKm Distance in kilometers
     * @param Vehicle|null $vehicle Vehicle to calculate for
     * @param string|null $region State code for regional fuel prices
     * @return array Fuel cost breakdown
     */
    public function calculateFuelCost(float $distanceKm, ?Vehicle $vehicle = null, ?string $region = null): array
    {
        if (!$vehicle) {
            // Default calculation without vehicle
            $fuelType = 'diesel';
            $consumptionPerKm = 0.20; // Default: 20L per 100km
            $fuelPrice = $this->getFuelPrice($fuelType, $region) ?? 5.50; // Default price
        } else {
            $fuelType = $vehicle->getFuelType();
            $consumptionPerKm = $vehicle->getFuelConsumptionPerKm();
            $fuelPrice = $this->getFuelPrice($fuelType, $region);
            
            if (!$fuelPrice) {
                // Fallback to default prices if not found
                $fuelPrice = $this->getDefaultFuelPrice($fuelType);
                Log::warning('Fuel price not found, using default', [
                    'fuel_type' => $fuelType,
                    'region' => $region,
                    'vehicle_id' => $vehicle->id,
                ]);
            }
        }

        // Calculate fuel consumption
        $fuelConsumptionLiters = $distanceKm * $consumptionPerKm;
        
        // Calculate total cost
        $totalCost = $fuelConsumptionLiters * $fuelPrice;

        return [
            'distance_km' => round($distanceKm, 2),
            'fuel_type' => $fuelType,
            'consumption_per_km' => round($consumptionPerKm, 4),
            'fuel_consumption_liters' => round($fuelConsumptionLiters, 2),
            'fuel_price_per_liter' => round($fuelPrice, 4),
            'total_cost' => round($totalCost, 2),
            'is_estimated' => !$this->getFuelPrice($fuelType, $region),
        ];
    }

    /**
     * Get current fuel price for a fuel type and region
     * 
     * @param string $fuelType
     * @param string|null $region
     * @return float|null
     */
    public function getFuelPrice(string $fuelType, ?string $region = null): ?float
    {
        $fuelPrice = FuelPrice::getCurrentPrice($fuelType, $region);
        
        if ($fuelPrice) {
            return (float) $fuelPrice->price_per_liter;
        }

        return null;
    }

    /**
     * Get default fuel price when not found in database
     * 
     * @param string $fuelType
     * @return float
     */
    protected function getDefaultFuelPrice(string $fuelType): float
    {
        // Default prices in BRL (approximate Brazilian prices as of 2024)
        return match($fuelType) {
            'diesel' => 5.50,      // Diesel S10
            'gasoline' => 5.80,     // Gasolina comum
            'ethanol' => 3.90,      // Etanol
            'cng' => 3.50,          // GNV
            default => 5.50,        // Default to diesel
        };
    }

    /**
     * Calculate fuel cost for multiple routes and compare
     * 
     * @param array $routes Array of route data with distance
     * @param Vehicle|null $vehicle
     * @param string|null $region
     * @return array Routes with fuel costs added
     */
    public function calculateFuelCostsForRoutes(array $routes, ?Vehicle $vehicle = null, ?string $region = null): array
    {
        foreach ($routes as &$route) {
            $distanceKm = ($route['distance'] ?? 0) / 1000; // Convert meters to km
            $fuelCost = $this->calculateFuelCost($distanceKm, $vehicle, $region);
            
            $route['fuel_cost'] = $fuelCost['total_cost'];
            $route['fuel_cost_breakdown'] = $fuelCost;
        }

        return $routes;
    }
}




