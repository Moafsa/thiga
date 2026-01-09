<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapsApiUsage extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'provider',
        'operation',
        'date',
        'requests',
        'successful',
        'failed',
        'estimated_cost',
    ];

    protected $casts = [
        'date' => 'date',
        'requests' => 'integer',
        'successful' => 'integer',
        'failed' => 'integer',
        'estimated_cost' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment usage counter
     */
    public static function incrementUsage(
        string $provider,
        string $operation,
        bool $success = true,
        ?int $tenantId = null,
        ?int $userId = null
    ): void {
        $date = now()->startOfDay();
        
        $usage = static::firstOrNew([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'provider' => $provider,
            'operation' => $operation,
            'date' => $date,
        ], [
            'requests' => 0,
            'successful' => 0,
            'failed' => 0,
            'estimated_cost' => 0,
        ]);

        $usage->requests++;
        
        if ($success) {
            $usage->successful++;
        } else {
            $usage->failed++;
        }

        // Calculate estimated cost
        $costPerRequest = static::getCostPerRequest($provider, $operation);
        $usage->estimated_cost += $costPerRequest;

        $usage->save();
    }

    /**
     * Get cost per request (in BRL)
     */
    protected static function getCostPerRequest(string $provider, string $operation): float
    {
        $prices = [
            'mapbox' => [
                'geocode' => 0.005, // $0.005 USD = ~R$ 0.025
                'reverse_geocode' => 0.005,
                'route' => 0.01, // $0.01 USD = ~R$ 0.05
                'distance_matrix' => 0.01,
            ],
            'google' => [
                'geocode' => 0.25, // R$ 0.25 per 1000 = R$ 0.00025 per request
                'reverse_geocode' => 0.25,
                'route' => 0.25,
                'distance_matrix' => 0.25,
            ],
        ];

        return $prices[$provider][$operation] ?? 0.01;
    }

    /**
     * Get today's usage for a user
     */
    public static function getTodayUsage(?int $userId = null, ?int $tenantId = null): array
    {
        $query = static::where('date', now()->startOfDay());
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->selectRaw('
                provider,
                operation,
                SUM(requests) as total_requests,
                SUM(successful) as total_successful,
                SUM(failed) as total_failed,
                SUM(estimated_cost) as total_cost
            ')
            ->groupBy('provider', 'operation')
            ->get()
            ->toArray();
    }
}
