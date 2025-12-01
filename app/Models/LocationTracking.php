<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'driver_id',
        'route_id',
        'shipment_id',
        'latitude',
        'longitude',
        'accuracy',
        'altitude',
        'speed',
        'heading',
        'is_moving',
        'tracked_at',
        'device_id',
        'app_version',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'is_moving' => 'boolean',
        'tracked_at' => 'datetime',
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

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByRoute($query, int $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    public function scopeByShipment($query, int $shipmentId)
    {
        return $query->where('shipment_id', $shipmentId);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('tracked_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeMoving($query)
    {
        return $query->where('is_moving', true);
    }

    public function scopeStationary($query)
    {
        return $query->where('is_moving', false);
    }

    public function getFormattedLocationAttribute(): string
    {
        return "Lat: {$this->latitude}, Lng: {$this->longitude}";
    }

    public function getFormattedSpeedAttribute(): string
    {
        return $this->speed ? number_format($this->speed, 1) . ' km/h' : 'N/A';
    }

    public function getFormattedAccuracyAttribute(): string
    {
        return $this->accuracy ? number_format($this->accuracy, 1) . 'm' : 'N/A';
    }

    public function getFormattedHeadingAttribute(): string
    {
        if (!$this->heading) {
            return 'N/A';
        }

        $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        $index = round($this->heading / 45) % 8;
        
        return $directions[$index] . " ({$this->heading}°)";
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->tracked_at->diffForHumans();
    }
}
