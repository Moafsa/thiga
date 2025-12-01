<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'code',
        'email',
        'phone',
        'postal_code',
        'address',
        'address_number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'settings',
        'is_active',
        'is_operational',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_operational' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOperational($query)
    {
        return $query->where('is_operational', true);
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->address_number}, {$this->neighborhood}, {$this->city}/{$this->state}";
    }
}
