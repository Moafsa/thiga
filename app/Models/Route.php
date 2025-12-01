<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'start_address',
        'start_city',
        'start_state',
        'start_zip_code',
        'start_address_type',
        'driver_id',
        'vehicle_id',
        'name',
        'description',
        'scheduled_date',
        'start_time',
        'end_time',
        'status',
        'started_at',
        'completed_at',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'estimated_distance',
        'estimated_duration',
        'settings',
        'route_options',
        'selected_route_option',
        'is_route_locked',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'start_latitude' => 'decimal:8',
        'start_longitude' => 'decimal:8',
        'end_latitude' => 'decimal:8',
        'end_longitude' => 'decimal:8',
        'estimated_distance' => 'decimal:2',
        'settings' => 'array',
        'route_options' => 'array',
        'is_route_locked' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the expenses for this route.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function locationTrackings(): HasMany
    {
        return $this->hasMany(LocationTracking::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeScheduledFor($query, string $date)
    {
        return $query->where('scheduled_date', $date);
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Agendada',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
            default => 'Desconhecido'
        };
    }

    public function getFormattedDistanceAttribute(): string
    {
        return $this->estimated_distance ? number_format($this->estimated_distance, 2, ',', '.') . ' km' : 'N/A';
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->estimated_duration) {
            return 'N/A';
        }
        
        $hours = floor($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        
        return "{$minutes}min";
    }

    /**
     * Check if route can be modified (not locked or user is admin)
     */
    public function canBeModified(?User $user = null): bool
    {
        if (!$this->is_route_locked) {
            return true;
        }

        // Admin can always modify
        if ($user && ($user->isTenantAdmin() || $user->isSuperAdmin())) {
            return true;
        }

        return false;
    }

    /**
     * Get selected route option data
     */
    public function getSelectedRouteOptionData(): ?array
    {
        if (!$this->selected_route_option || !$this->route_options) {
            return null;
        }

        $index = $this->selected_route_option - 1; // Convert to 0-based index
        return $this->route_options[$index] ?? null;
    }
}
