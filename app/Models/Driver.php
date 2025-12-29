<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DriverTenantAssignment;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Driver extends Model
{
    use HasFactory, LogsActivity;

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
        'photo_url',
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
     * Get all photos for this driver
     */
    public function photos(): HasMany
    {
        return $this->hasMany(DriverPhoto::class)->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    /**
     * Get primary photo
     */
    public function primaryPhoto(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DriverPhoto::class)->where('is_primary', true);
    }

    /**
     * Get all expenses (proven expenses) for this driver
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(DriverExpense::class)->orderBy('expense_date', 'desc');
    }

    /**
     * Get approved expenses only
     */
    public function approvedExpenses(): HasMany
    {
        return $this->hasMany(DriverExpense::class)->where('status', 'approved');
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
            
            // Disable activity log if table doesn't exist
            try {
                if (!\Schema::hasTable('activity_log')) {
                    activity()->disableLogging();
                }
            } catch (\Exception $e) {
                // If we can't check the table, disable logging to be safe
                activity()->disableLogging();
            }
        });
        
        static::saved(function (Driver $driver) {
            // Re-enable logging after save
            try {
                activity()->enableLogging();
            } catch (\Exception $e) {
                // Ignore errors when re-enabling
            }
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

    /**
     * Get photo URL for display (with fallback to avatar)
     * Uses MinIO if available, otherwise public disk
     */
    public function getDisplayPhotoUrl(): string
    {
        // Try primary photo first
        try {
            $primaryPhoto = $this->primaryPhoto;
            if ($primaryPhoto && $primaryPhoto->photo_url) {
                $primaryUrl = $primaryPhoto->url;
                if ($primaryUrl) {
                    return $primaryUrl;
                }
            }
        } catch (\Exception $e) {
            \Log::debug('Error getting primary photo URL', [
                'driver_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback to photo_url field
        if ($this->attributes['photo_url'] ?? null) {
            $photoPath = $this->attributes['photo_url'];
            
            // Try MinIO first
            try {
                $minioConfig = config('filesystems.disks.minio');
                if ($minioConfig && \Storage::disk('minio')->exists($photoPath)) {
                    // Build URL manually for path-style endpoint
                    $baseUrl = rtrim($minioConfig['url'] ?? '', '/');
                    $bucket = $minioConfig['bucket'] ?? '';
                    $path = ltrim($photoPath, '/');
                    $minioUrl = "{$baseUrl}/{$bucket}/{$path}";
                    
                    // Validate that URL was generated successfully
                    if (filter_var($minioUrl, FILTER_VALIDATE_URL)) {
                        return $minioUrl;
                    }
                }
            } catch (\Exception $e) {
                \Log::debug('Failed to get MinIO URL for driver photo', [
                    'driver_id' => $this->id,
                    'path' => $photoPath,
                    'error' => $e->getMessage(),
                ]);
            }

            // Fallback to public disk
            try {
                if (\Storage::disk('public')->exists($photoPath)) {
                    return \Storage::disk('public')->url($photoPath);
                }
            } catch (\Exception $e) {
                \Log::debug('Failed to get public disk URL for driver photo', [
                    'driver_id' => $this->id,
                    'path' => $photoPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=FF6B35&color=fff&size=150';
    }

    /**
     * Get the full photo URL if exists
     * Uses MinIO if available, otherwise public disk
     */
    public function getPhotoUrl(): ?string
    {
        if ($this->attributes['photo_url'] ?? null) {
            $photoPath = $this->attributes['photo_url'];
            
            // Try MinIO first
            try {
                if (\Storage::disk('minio')->exists($photoPath)) {
                    return \Storage::disk('minio')->url($photoPath);
                }
            } catch (\Exception $e) {
                // MinIO not available
            }

            // Fallback to public disk
            if (\Storage::disk('public')->exists($photoPath)) {
                return \Storage::disk('public')->url($photoPath);
            }
        }
        return null;
    }

    /**
     * Get storage disk for photos (MinIO if available, otherwise public)
     * @deprecated Use DriverPhotoService::getStorageDisk() instead
     */
    public static function getPhotoStorageDisk(): string
    {
        return \App\Services\DriverPhotoService::getStorageDisk();
    }

    public function isOnBreak(): bool
    {
        return $this->status === 'on_break';
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DriverTenantAssignment::class);
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
        // Use attributes array to avoid recursion
        if (!isset($this->attributes['last_location_update']) || !$this->attributes['last_location_update']) {
            return 'Nunca';
        }

        return \Carbon\Carbon::parse($this->attributes['last_location_update'])->diffForHumans();
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'cnh_number', 'cnh_category', 'cnh_expiry_date', 'vehicle_plate', 'vehicle_model', 'vehicle_color', 'photo_url'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Driver {$eventName}");
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