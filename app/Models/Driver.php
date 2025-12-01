<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'email',
        'phone',
        'document',
        'cnh_number',
        'cnh_category',
        'cnh_expiry_date',
        'vehicle_plate',
        'vehicle_model',
        'vehicle_color',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'status',
        'is_active',
        'location_tracking_enabled',
        'settings',
        'phone_e164',
    ];

    protected $casts = [
        'cnh_expiry_date' => 'date',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'last_location_update' => 'datetime',
        'is_active' => 'boolean',
        'location_tracking_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function locationTrackings(): HasMany
    {
        return $this->hasMany(LocationTracking::class);
    }

    public function deliveryProofs(): HasMany
    {
        return $this->hasMany(DeliveryProof::class);
    }

    /**
     * Get the vehicles that this driver can drive (many-to-many).
     */
    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'driver_vehicle')
            ->withPivot('assigned_at', 'unassigned_at', 'is_active', 'can_drive', 'notes')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * Get all vehicles (including inactive assignments).
     */
    public function allVehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'driver_vehicle')
            ->withPivot('assigned_at', 'unassigned_at', 'is_active', 'can_drive', 'notes')
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saving(function (Driver $driver) {
            $driver->phone_e164 = self::normalizePhone($driver->phone);
        });
    }

    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return $digits;
        }

        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
            return '55' . $digits;
        }

        return $digits;
    }

    public function scopeByPhoneE164($query, string $phone)
    {
        return $query->where('phone_e164', $phone);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithLocationTracking($query)
    {
        return $query->where('location_tracking_enabled', true);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function isBusy(): bool
    {
        return $this->status === 'busy';
    }

    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }

    public function isOnBreak(): bool
    {
        return $this->status === 'on_break';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Disponível',
            'busy' => 'Ocupado',
            'offline' => 'Offline',
            'on_break' => 'Em Pausa',
            default => 'Desconhecido'
        };
    }

    public function getFormattedLocationAttribute(): string
    {
        if (!$this->current_latitude || !$this->current_longitude) {
            return 'Localização não disponível';
        }

        return "Lat: {$this->current_latitude}, Lng: {$this->current_longitude}";
    }

    public function getLastLocationUpdateAttribute(): string
    {
        if (!$this->last_location_update) {
            return 'Nunca';
        }

        return $this->last_location_update->diffForHumans();
    }

    public function updateLocation(float $latitude, float $longitude, array $metadata = []): void
    {
        $this->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'last_location_update' => now(),
        ]);

        // Create location tracking record
        LocationTracking::create([
            'tenant_id' => $this->tenant_id,
            'driver_id' => $this->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'tracked_at' => now(),
            'metadata' => $metadata,
        ]);
    }
}
