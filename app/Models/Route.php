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
        'planned_path',
        'actual_path',
        'path_updated_at',
        'notes',
        // Time control
        'planned_departure_datetime',
        'planned_arrival_datetime',
        'actual_departure_datetime',
        'actual_arrival_datetime',
        // Driver per diem
        'driver_diarias_count',
        'driver_diaria_value',
        // Deposits
        'deposit_toll',
        'deposit_expenses',
        'deposit_fuel',
        // Revenue
        'total_revenue',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'planned_departure_datetime' => 'datetime',
        'planned_arrival_datetime' => 'datetime',
        'actual_departure_datetime' => 'datetime',
        'actual_arrival_datetime' => 'datetime',
        'start_latitude' => 'decimal:8',
        'start_longitude' => 'decimal:8',
        'end_latitude' => 'decimal:8',
        'end_longitude' => 'decimal:8',
        'estimated_distance' => 'decimal:2',
        'driver_diarias_count' => 'integer',
        'driver_diaria_value' => 'decimal:2',
        'deposit_toll' => 'decimal:2',
        'deposit_expenses' => 'decimal:2',
        'deposit_fuel' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'settings' => 'array',
        'route_options' => 'array',
        'is_route_locked' => 'boolean',
        'planned_path' => 'array',
        'actual_path' => 'array',
        'path_updated_at' => 'datetime',
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

    /**
     * Get ordered shipments by sequential optimization
     */
    public function getOrderedShipmentsBySequentialOptimization(): \Illuminate\Support\Collection
    {
        $optimizedOrder = $this->settings['sequential_optimized_order'] ?? [];

        if (empty($optimizedOrder)) {
            return $this->shipments()->orderBy('id')->get();
        }

        // Create a map of shipment ID to shipment object
        $shipmentsMap = $this->shipments->keyBy('id');

        // Reorder shipments based on the optimized order
        $ordered = collect();
        foreach ($optimizedOrder as $shipmentId) {
            if (isset($shipmentsMap[$shipmentId])) {
                $ordered->push($shipmentsMap[$shipmentId]);
            }
        }

        return $ordered;
    }

    /**
     * Calculate and update total revenue from shipments
     */
    public function calculateTotalRevenue(): void
    {
        $totalRevenue = $this->shipments()->sum('value') ?? 0;
        $this->update(['total_revenue' => $totalRevenue]);
    }

    /**
     * Generate Google Maps directions URL for this route
     * 
     * @return string|null Google Maps URL or null if route doesn't have required coordinates
     */
    public function getGoogleMapsUrl(): ?string
    {
        // Origin must be depot/branch (start coordinates)
        if (!$this->start_latitude || !$this->start_longitude) {
            return null;
        }

        $origin = "{$this->start_latitude},{$this->start_longitude}";
        
        // Get shipments with delivery coordinates
        $shipments = $this->shipments()
            ->whereNotNull('delivery_latitude')
            ->whereNotNull('delivery_longitude')
            ->get();

        if ($shipments->isEmpty()) {
            // If no shipments, return simple route from origin to origin
            return "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$origin}&travelmode=driving";
        }

        // Build waypoints from delivery addresses
        // Use optimized order if available, otherwise use shipment order
        $orderedShipments = $this->getOrderedShipmentsBySequentialOptimization();
        
        // Filter to only shipments with coordinates
        $orderedShipments = $orderedShipments->filter(function($shipment) {
            return $shipment->delivery_latitude && $shipment->delivery_longitude;
        });

        // If no ordered shipments with coordinates, use regular shipments
        if ($orderedShipments->isEmpty()) {
            $orderedShipments = $shipments;
        }

        // Build waypoints string
        $waypoints = $orderedShipments->map(function($shipment) {
            return "{$shipment->delivery_latitude},{$shipment->delivery_longitude}";
        })->implode('|');

        // Destination is always the origin (return to depot/branch)
        $destination = $origin;

        // Build Google Maps directions URL
        // Format: https://www.google.com/maps/dir/?api=1&origin=...&destination=...&waypoints=...|...&travelmode=driving
        $url = "https://www.google.com/maps/dir/?api=1";
        $url .= "&origin=" . urlencode($origin);
        $url .= "&destination=" . urlencode($destination);
        
        if ($waypoints) {
            $url .= "&waypoints=" . urlencode($waypoints);
        }
        
        $url .= "&travelmode=driving";

        return $url;
    }
}
