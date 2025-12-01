<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'asaas_customer_id',
        'asaas_subscription_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'amount',
        'billing_cycle',
        'features',
        'limits',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'amount' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial' && 
               ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    public function canUseFeature(string $feature): bool
    {
        if (!$this->isActive() && !$this->isTrial()) {
            return false;
        }

        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    public function getLimit(string $limit): int
    {
        $limits = $this->limits ?? [];
        return $limits[$limit] ?? 0;
    }
}
