<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'plate',
        'renavam',
        'chassis',
        'brand',
        'model',
        'year',
        'color',
        'fuel_type',
        'vehicle_type',
        'capacity_weight',
        'capacity_volume',
        'axles',
        'status',
        'is_active',
        'ownership_type',
        'insurance_expiry_date',
        'inspection_expiry_date',
        'registration_expiry_date',
        'current_odometer',
        'last_maintenance_odometer',
        'last_maintenance_date',
        'maintenance_interval_km',
        'maintenance_interval_days',
        'fuel_consumption_per_km',
        'tank_capacity',
        'average_fuel_consumption',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'capacity_weight' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'insurance_expiry_date' => 'date',
        'inspection_expiry_date' => 'date',
        'registration_expiry_date' => 'date',
        'current_odometer' => 'integer',
        'last_maintenance_odometer' => 'integer',
        'last_maintenance_date' => 'date',
        'maintenance_interval_km' => 'integer',
        'maintenance_interval_days' => 'integer',
        'fuel_consumption_per_km' => 'decimal:4',
        'tank_capacity' => 'decimal:2',
        'average_fuel_consumption' => 'decimal:4',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Check if vehicle is fleet (can have maintenance and expenses).
     */
    public function isFleet(): bool
    {
        return $this->ownership_type === 'fleet';
    }

    /**
     * Check if vehicle is third party (cannot have maintenance or expenses).
     */
    public function isThirdParty(): bool
    {
        return $this->ownership_type === 'third_party';
    }

    /**
     * Get the tenant that owns the vehicle.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the drivers that can drive this vehicle (many-to-many).
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'driver_vehicle')
            ->withPivot('assigned_at', 'unassigned_at', 'is_active', 'can_drive', 'notes')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * Get all drivers (including inactive assignments).
     */
    public function allDrivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'driver_vehicle')
            ->withPivot('assigned_at', 'unassigned_at', 'is_active', 'can_drive', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the routes for this vehicle.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get the expenses (maintenances) for this vehicle.
     * Only fleet vehicles can have expenses.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get maintenance expenses for this vehicle.
     */
    public function maintenances()
    {
        return $this->expenses()->whereHas('category', function($query) {
            $query->where('name', 'Manutenção');
        });
    }

    /**
     * Get fuel refueling expenses for this vehicle.
     */
    public function fuelRefuelings()
    {
        return $this->expenses()
            ->whereNotNull('fuel_liters')
            ->whereNotNull('odometer_reading')
            ->orderBy('odometer_reading', 'asc');
    }

    /**
     * Get recent fuel refuelings (last N records).
     */
    public function recentFuelRefuelings(int $limit = 10)
    {
        return $this->fuelRefuelings()
            ->orderBy('odometer_reading', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the maintenances for this vehicle.
     * Note: VehicleMaintenance model will be created in Priority 6
     */
    // public function maintenances(): HasMany
    // {
    //     return $this->hasMany(VehicleMaintenance::class);
    // }

    /**
     * Check if vehicle is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    /**
     * Check if vehicle is in use.
     */
    public function isInUse(): bool
    {
        return $this->status === 'in_use';
    }

    /**
     * Check if vehicle is in maintenance.
     */
    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    /**
     * Check if maintenance is due.
     */
    public function isMaintenanceDue(): bool
    {
        if (!$this->last_maintenance_date || !$this->maintenance_interval_km || !$this->maintenance_interval_days) {
            return false;
        }

        $kmOverdue = $this->current_odometer - ($this->last_maintenance_odometer ?? 0) >= $this->maintenance_interval_km;
        $daysOverdue = $this->last_maintenance_date->addDays($this->maintenance_interval_days)->isPast();

        return $kmOverdue || $daysOverdue;
    }

    /**
     * Get days until next maintenance.
     */
    public function getDaysUntilMaintenance(): ?int
    {
        if (!$this->last_maintenance_date || !$this->maintenance_interval_days) {
            return null;
        }

        $nextMaintenanceDate = $this->last_maintenance_date->copy()->addDays($this->maintenance_interval_days);
        return now()->diffInDays($nextMaintenanceDate, false);
    }

    /**
     * Get km until next maintenance.
     */
    public function getKmUntilMaintenance(): ?int
    {
        if (!$this->maintenance_interval_km || !$this->last_maintenance_odometer) {
            return null;
        }

        $kmSinceLastMaintenance = $this->current_odometer - $this->last_maintenance_odometer;
        return max(0, $this->maintenance_interval_km - $kmSinceLastMaintenance);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Disponível',
            'in_use' => 'Em Uso',
            'maintenance' => 'Em Manutenção',
            'inactive' => 'Inativo',
            default => 'Desconhecido'
        };
    }

    /**
     * Get formatted plate (Brazilian format: ABC-1234 or ABC1D23).
     */
    public function getFormattedPlateAttribute(): string
    {
        $plate = strtoupper(preg_replace('/[^A-Z0-9]/', '', $this->plate));
        
        if (strlen($plate) === 7) {
            // Old format: ABC-1234
            return substr($plate, 0, 3) . '-' . substr($plate, 3);
        } elseif (strlen($plate) === 8) {
            // New format: ABC1D23
            return substr($plate, 0, 3) . substr($plate, 3, 1) . substr($plate, 4, 1) . substr($plate, 5);
        }
        
        return $this->plate;
    }

    /**
     * Scope to filter available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }


    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get fuel consumption per km (uses specific or average)
     * Returns L/km for cost calculations
     */
    public function getFuelConsumptionPerKm(): float
    {
        if ($this->fuel_consumption_per_km) {
            // If stored as L/km, return directly
            return (float) $this->fuel_consumption_per_km;
        }

        // If average_fuel_consumption is stored as km/L, convert to L/km
        if ($this->average_fuel_consumption) {
            // Convert km/L to L/km
            return round(1 / (float) $this->average_fuel_consumption, 4);
        }

        // Default consumption by vehicle type (liters per km)
        return match($this->vehicle_type) {
            'truck' => 0.35,  // 35L per 100km = 0.35L/km
            'van' => 0.12,    // 12L per 100km = 0.12L/km
            'car' => 0.10,    // 10L per 100km = 0.10L/km
            default => 0.20,  // Default: 20L per 100km = 0.20L/km
        };
    }

    /**
     * Get fuel consumption in km/L (kilometers per liter)
     * This is the user-friendly format
     */
    public function getFuelConsumptionKmPerLiter(): ?float
    {
        // If average_fuel_consumption is stored as km/L, return it
        if ($this->average_fuel_consumption) {
            return (float) $this->average_fuel_consumption;
        }

        // If fuel_consumption_per_km is stored as L/km, convert to km/L
        if ($this->fuel_consumption_per_km) {
            return round(1 / (float) $this->fuel_consumption_per_km, 2);
        }

        // Default consumption by vehicle type (convert L/km to km/L)
        $litersPerKm = match($this->vehicle_type) {
            'truck' => 0.35,  // 35L per 100km
            'van' => 0.12,    // 12L per 100km
            'car' => 0.10,    // 10L per 100km
            default => 0.20,  // Default: 20L per 100km
        };

        return round(1 / $litersPerKm, 2);
    }

    /**
     * Get fuel type (defaults to diesel for trucks, gasoline for cars)
     */
    public function getFuelType(): string
    {
        if ($this->fuel_type) {
            return $this->fuel_type;
        }

        // Default fuel type by vehicle type
        return match($this->vehicle_type) {
            'truck', 'van', 'bus' => 'diesel',
            'car' => 'gasoline',
            default => 'diesel',
        };
    }

    /**
     * Calculate real fuel consumption based on refueling history.
     * Returns consumption in km/L (kilometers per liter) or null if insufficient data.
     */
    public function calculateRealFuelConsumption(): ?float
    {
        $refuelings = $this->fuelRefuelings()->get();

        if ($refuelings->count() < 2) {
            return null; // Need at least 2 refuelings to calculate
        }

        // Sort by odometer reading
        $refuelings = $refuelings->sortBy('odometer_reading');

        $totalLiters = 0;
        $totalKm = 0;
        $previousOdometer = null;

        foreach ($refuelings as $refueling) {
            if ($previousOdometer !== null) {
                $kmDriven = $refueling->odometer_reading - $previousOdometer;
                if ($kmDriven > 0) {
                    $totalKm += $kmDriven;
                    $totalLiters += $refueling->fuel_liters;
                }
            }
            $previousOdometer = $refueling->odometer_reading;
        }

        if ($totalKm > 0 && $totalLiters > 0) {
            // Return km per liter (km/L)
            return round($totalKm / $totalLiters, 2);
        }

        return null;
    }

    /**
     * Get average fuel consumption from history and update average_fuel_consumption field.
     */
    public function updateAverageFuelConsumption(): bool
    {
        $realConsumption = $this->calculateRealFuelConsumption();

        if ($realConsumption !== null) {
            $this->update(['average_fuel_consumption' => $realConsumption]);
            return true;
        }

        return false;
    }

    /**
     * Get fuel consumption statistics.
     */
    public function getFuelConsumptionStats(): array
    {
        $refuelings = $this->fuelRefuelings()->get();

        if ($refuelings->isEmpty()) {
            return [
                'total_refuelings' => 0,
                'total_liters' => 0,
                'average_consumption_km_per_liter' => null,
                'last_refueling_date' => null,
                'last_odometer' => null,
            ];
        }

        $totalLiters = $refuelings->sum('fuel_liters');
        $realConsumption = $this->calculateRealFuelConsumption(); // Returns km/L
        $lastRefueling = $refuelings->sortByDesc('odometer_reading')->first();

        return [
            'total_refuelings' => $refuelings->count(),
            'total_liters' => round($totalLiters, 2),
            'average_consumption_km_per_liter' => $realConsumption, // km/L
            'last_refueling_date' => $lastRefueling->due_date,
            'last_odometer' => $lastRefueling->odometer_reading,
        ];
    }
}

