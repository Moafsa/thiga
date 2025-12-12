<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TollPlaza extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'highway',
        'city',
        'state',
        'latitude',
        'longitude',
        'price_car',
        'price_van',
        'price_truck_2_axles',
        'price_truck_3_axles',
        'price_truck_4_axles',
        'price_truck_5_axles',
        'price_bus',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'price_car' => 'decimal:2',
        'price_van' => 'decimal:2',
        'price_truck_2_axles' => 'decimal:2',
        'price_truck_3_axles' => 'decimal:2',
        'price_truck_4_axles' => 'decimal:2',
        'price_truck_5_axles' => 'decimal:2',
        'price_bus' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get toll price for a specific vehicle type
     * 
     * @param string $vehicleType Vehicle type (car, van, truck, bus)
     * @param int|null $axles Number of axles (for trucks)
     * @return float
     */
    public function getPriceForVehicle(string $vehicleType, ?int $axles = null): float
    {
        return match($vehicleType) {
            'car', 'Carro', 'Automóvel' => $this->price_car,
            'van', 'Van' => $this->price_van,
            'truck', 'Caminhão', 'Truck' => match($axles) {
                2 => $this->price_truck_2_axles,
                3 => $this->price_truck_3_axles,
                4 => $this->price_truck_4_axles,
                5, 6, 7, 8, 9, 10 => $this->price_truck_5_axles,
                default => $this->price_truck_2_axles, // Default to 2 axles
            },
            'bus', 'Ônibus' => $this->price_bus,
            default => $this->price_car, // Default to car price
        };
    }

    /**
     * Scope to find toll plazas near coordinates
     */
    public function scopeNearCoordinates($query, float $latitude, float $longitude, float $radiusKm = 5.0)
    {
        // Using Haversine formula approximation
        // 1 degree latitude ≈ 111 km
        // 1 degree longitude ≈ 111 km * cos(latitude)
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * cos(deg2rad($latitude)));

        return $query->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta])
            ->where('is_active', true);
    }

    /**
     * Calculate distance to coordinates in kilometers
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return PHP_FLOAT_MAX;
        }

        $earthRadius = 6371; // Earth radius in kilometers

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
















