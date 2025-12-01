<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

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
     * Scope to filter active vehicles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}

