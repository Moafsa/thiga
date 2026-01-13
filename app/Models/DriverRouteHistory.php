<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DriverRouteHistory extends Model
{
    use HasFactory;

    protected $table = 'driver_route_history';

    protected $fillable = [
        'tenant_id',
        'driver_id',
        'route_id',
        'vehicle_id',
        'route_name',
        'route_description',
        'scheduled_date',
        'started_at',
        'completed_at',
        'status',
        'route_type',
        'total_shipments',
        'delivered_shipments',
        'picked_up_shipments',
        'exception_shipments',
        'planned_distance_km',
        'actual_distance_km',
        'planned_duration_minutes',
        'actual_duration_minutes',
        'stops_count',
        'efficiency_score',
        'average_speed_kmh',
        'fuel_efficiency_km_l',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'actual_path_snapshot',
        'planned_path_snapshot',
        'total_deviation_km',
        'deviation_count',
        'stops_duration_minutes',
        'total_revenue',
        'driver_diarias_amount',
        'total_expenses',
        'net_profit',
        'achievements',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'planned_distance_km' => 'decimal:2',
        'actual_distance_km' => 'decimal:2',
        'planned_duration_minutes' => 'integer',
        'actual_duration_minutes' => 'integer',
        'stops_count' => 'integer',
        'efficiency_score' => 'decimal:2',
        'average_speed_kmh' => 'decimal:2',
        'fuel_efficiency_km_l' => 'decimal:2',
        'start_latitude' => 'decimal:8',
        'start_longitude' => 'decimal:8',
        'end_latitude' => 'decimal:8',
        'end_longitude' => 'decimal:8',
        'actual_path_snapshot' => 'array',
        'planned_path_snapshot' => 'array',
        'total_deviation_km' => 'decimal:2',
        'deviation_count' => 'integer',
        'stops_duration_minutes' => 'integer',
        'total_revenue' => 'decimal:2',
        'driver_diarias_amount' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'achievements' => 'array',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope para rotas do motorista
     */
    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope para rotas completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para período
     */
    public function scopeInPeriod($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('completed_at', [$startDate, $endDate]);
    }

    /**
     * Scope para rotas recentes
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('completed_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->actual_duration_minutes) {
            return 'N/A';
        }
        
        $hours = floor($this->actual_duration_minutes / 60);
        $minutes = $this->actual_duration_minutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        
        return "{$minutes}min";
    }

    /**
     * Get formatted distance
     */
    public function getFormattedDistanceAttribute(): string
    {
        $distance = $this->actual_distance_km ?? $this->planned_distance_km;
        if (!$distance) {
            return 'N/A';
        }
        
        return number_format($distance, 2, ',', '.') . ' km';
    }

    /**
     * Get success rate (percentage of successful deliveries)
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_shipments === 0) {
            return 0;
        }
        
        $successful = $this->delivered_shipments + $this->picked_up_shipments;
        return round(($successful / $this->total_shipments) * 100, 2);
    }

    /**
     * Get efficiency badge color
     */
    public function getEfficiencyBadgeColorAttribute(): string
    {
        $score = $this->efficiency_score ?? 0;
        
        if ($score >= 90) return 'green';
        if ($score >= 75) return 'blue';
        if ($score >= 60) return 'yellow';
        return 'red';
    }

    /**
     * Check if route was on time
     */
    public function isOnTime(): bool
    {
        if (!$this->planned_duration_minutes || !$this->actual_duration_minutes) {
            return false;
        }
        
        // Considera no prazo se não excedeu 20% do tempo planejado
        $threshold = $this->planned_duration_minutes * 1.2;
        return $this->actual_duration_minutes <= $threshold;
    }

    /**
     * Check if route was efficient (low deviation)
     */
    public function isEfficient(): bool
    {
        if (!$this->planned_distance_km || !$this->actual_distance_km) {
            return false;
        }
        
        // Considera eficiente se não excedeu 15% da distância planejada
        $threshold = $this->planned_distance_km * 1.15;
        return $this->actual_distance_km <= $threshold;
    }

    /**
     * Get achievement badges
     */
    public function getAchievementBadgesAttribute(): array
    {
        $badges = [];
        
        if ($this->isOnTime()) {
            $badges[] = [
                'name' => 'on_time',
                'label' => 'No Prazo',
                'icon' => 'clock',
                'color' => 'green',
            ];
        }
        
        if ($this->isEfficient()) {
            $badges[] = [
                'name' => 'efficient',
                'label' => 'Eficiente',
                'icon' => 'route',
                'color' => 'blue',
            ];
        }
        
        if ($this->success_rate >= 100) {
            $badges[] = [
                'name' => 'perfect',
                'label' => 'Perfeito',
                'icon' => 'star',
                'color' => 'gold',
            ];
        }
        
        if ($this->efficiency_score >= 90) {
            $badges[] = [
                'name' => 'high_score',
                'label' => 'Alta Performance',
                'icon' => 'trophy',
                'color' => 'purple',
            ];
        }
        
        if ($this->total_shipments >= 10) {
            $badges[] = [
                'name' => 'many_deliveries',
                'label' => 'Muitas Entregas',
                'icon' => 'truck',
                'color' => 'orange',
            ];
        }
        
        return $badges;
    }
}
