<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'features',
        'limits',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    /**
     * Get the subscriptions for the plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if plan has specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Get limit for specific resource.
     */
    public function getLimit(string $limit): int
    {
        $limits = $this->limits ?? [];
        return $limits[$limit] ?? 0;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }


    /**
     * Scope for popular plans.
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Calculate split amount based on price
     * E.g., price=1000, split_percentage=10 => 100
     */
    public function calculateSplitAmount($amount): float
    {
        return round($amount * ($this->split_percentage / 100), 2);
    }

    /**
     * Get split percentage as float
     */
    public function getSplitPercentageAttribute(): float
    {
        return (float) $this->attributes['split_percentage'] ?? 0;
    }

    /**
     * Get net amount after split commission
     */
    public function getNetAmountAttribute(): float
    {
        return $this->price - $this->calculateSplitAmount($this->price);
    }

    /**
     * Scope: Filter plans with split commission
     */
    public function scopeWithSplit($query)
    {
        return $query->where('split_percentage', '>', 0);
    }

    /**
     * Scope: Filter plans without split commission
     */
    public function scopeWithoutSplit($query)
    {
        return $query->where('split_percentage', '=', 0);
    }

    /**
     * Get formatted split percentage
     */
    public function getFormattedSplitAttribute(): string
    {
        return number_format($this->split_percentage, 2, ',', '.') . '%';
    }

}
