<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Driver extends Model
{
    use HasFactory, HasActiveScope;

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

    /**
     * Get total payments (diarias) from completed routes
     * Cached for 5 minutes to improve performance
     */
    public function getTotalPayments(?string $cacheKey = null): float
    {
        $cacheKey = $cacheKey ?? "driver_{$this->id}_total_payments";
        
        return Cache::remember($cacheKey, 300, function () {
            return $this->routes()
                ->where('status', 'completed')
                ->whereNotNull('driver_diaria_value')
                ->whereNotNull('driver_diarias_count')
                ->get()
                ->sum(function ($route) {
                    return ($route->driver_diaria_value ?? 0) * ($route->driver_diarias_count ?? 0);
                });
        });
    }

    /**
     * Get total expenses from driver routes
     * Cached for 5 minutes to improve performance
     */
    public function getTotalExpenses(?string $cacheKey = null): float
    {
        $cacheKey = $cacheKey ?? "driver_{$this->id}_total_expenses";
        
        return Cache::remember($cacheKey, 300, function () {
            return Expense::whereHas('route', function ($query) {
                    $query->where('driver_id', $this->id);
                })
                ->where('status', 'paid')
                ->sum('amount');
        });
    }

    /**
     * Get wallet balance (payments - expenses)
     * Cached for 5 minutes to improve performance
     */
    public function getWalletBalance(?string $cacheKey = null): float
    {
        $cacheKey = $cacheKey ?? "driver_{$this->id}_wallet_balance";
        
        return Cache::remember($cacheKey, 300, function () {
            return $this->getTotalPayments() - $this->getTotalExpenses();
        });
    }

    /**
     * Clear driver financial cache
     */
    public function clearFinancialCache(): void
    {
        Cache::forget("driver_{$this->id}_total_payments");
        Cache::forget("driver_{$this->id}_total_expenses");
        Cache::forget("driver_{$this->id}_wallet_balance");
    }

    /**
     * Get recent payments with pagination and date range filter
     */
    public function getRecentPayments(?string $startDate = null, ?string $endDate = null, int $perPage = 15)
    {
        $query = $this->routes()
            ->where('status', 'completed')
            ->whereNotNull('driver_diaria_value')
            ->whereNotNull('driver_diarias_count');

        if ($startDate) {
            $query->where('completed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('completed_at', '<=', $endDate . ' 23:59:59');
        }

        if (!$startDate && !$endDate) {
            // Default to last 30 days if no dates provided
            $query->where('completed_at', '>=', now()->subDays(30));
        }

        return $query->orderBy('completed_at', 'desc')
            ->paginate($perPage)
            ->through(function ($route) {
                $total = ($route->driver_diaria_value ?? 0) * ($route->driver_diarias_count ?? 0);
                return [
                    'route_id' => $route->id,
                    'route_name' => $route->name,
                    'amount' => $total,
                    'diarias_count' => $route->driver_diarias_count ?? 0,
                    'diaria_value' => $route->driver_diaria_value ?? 0,
                    'date' => $route->completed_at,
                ];
            });
    }

    /**
     * Get recent expenses with pagination and date range filter
     */
    public function getRecentExpenses(?string $startDate = null, ?string $endDate = null, int $perPage = 15)
    {
        $query = Expense::whereHas('route', function ($q) {
                $q->where('driver_id', $this->id);
            })
            ->where('status', 'paid');

        if ($startDate) {
            $query->where('paid_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('paid_at', '<=', $endDate . ' 23:59:59');
        }

        if (!$startDate && !$endDate) {
            // Default to last 30 days if no dates provided
            $query->where('paid_at', '>=', now()->subDays(30));
        }

        return $query->orderBy('paid_at', 'desc')
            ->with(['route', 'category'])
            ->paginate($perPage)
            ->through(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'route_id' => $expense->route_id,
                    'route_name' => $expense->route?->name,
                    'category' => $expense->category?->name,
                    'date' => $expense->paid_at,
                ];
            });
    }

    /**
     * Get all financial history (payments + expenses) combined and sorted
     */
    public function getFinancialHistory(?string $startDate = null, ?string $endDate = null, int $perPage = 15)
    {
        $payments = $this->getRecentPayments($startDate, $endDate, 1000)->items();
        $expenses = $this->getRecentExpenses($startDate, $endDate, 1000)->items();

        $history = collect()
            ->merge(collect($payments)->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'id' => $payment['route_id'],
                    'description' => "Pagamento - {$payment['route_name']}",
                    'amount' => $payment['amount'],
                    'date' => $payment['date'],
                    'details' => $payment,
                ];
            }))
            ->merge(collect($expenses)->map(function ($expense) {
                return [
                    'type' => 'expense',
                    'id' => $expense['id'],
                    'description' => $expense['description'],
                    'amount' => $expense['amount'],
                    'date' => $expense['date'],
                    'details' => $expense,
                ];
            }))
            ->sortByDesc(function ($item) {
                return $item['date']->timestamp;
            })
            ->values();

        // Manual pagination
        $currentPage = request()->get('page', 1);
        $items = $history->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $history->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
