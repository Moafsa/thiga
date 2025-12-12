<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salesperson extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'email',
        'phone',
        'document',
        'commission_rate',
        'max_discount_percentage',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'max_discount_percentage' => 'decimal:2',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function canApplyDiscount(float $discountPercentage): bool
    {
        return $discountPercentage <= $this->max_discount_percentage;
    }

    public function getFormattedCommissionRateAttribute(): string
    {
        return number_format($this->commission_rate, 2, ',', '.') . '%';
    }

    public function getFormattedMaxDiscountAttribute(): string
    {
        return number_format($this->max_discount_percentage, 2, ',', '.') . '%';
    }
}
