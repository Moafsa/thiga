<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_type',
        'price_per_liter',
        'effective_date',
        'expires_at',
        'region',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'price_per_liter' => 'decimal:4',
        'effective_date' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get current active fuel price for a fuel type and region
     * 
     * @param string $fuelType
     * @param string|null $region State code (SP, RJ, etc) or null for national
     * @return FuelPrice|null
     */
    public static function getCurrentPrice(string $fuelType, ?string $region = null): ?self
    {
        $query = static::where('fuel_type', $fuelType)
            ->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });

        if ($region) {
            // Try region-specific first, then fallback to national (null region)
            $query->where(function($q) use ($region) {
                $q->where('region', $region)
                  ->orWhereNull('region');
            })->orderByRaw('CASE WHEN region IS NOT NULL THEN 0 ELSE 1 END');
        } else {
            $query->whereNull('region');
        }

        return $query->orderBy('effective_date', 'desc')->first();
    }

    /**
     * Scope to filter active prices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope to filter by fuel type
     */
    public function scopeByFuelType($query, string $fuelType)
    {
        return $query->where('fuel_type', $fuelType);
    }

    /**
     * Scope to filter by region
     */
    public function scopeByRegion($query, ?string $region)
    {
        if ($region) {
            return $query->where(function($q) use ($region) {
                $q->where('region', $region)
                  ->orWhereNull('region');
            });
        }
        return $query->whereNull('region');
    }
}































