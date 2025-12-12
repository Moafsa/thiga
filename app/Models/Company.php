<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'name',
        'trade_name',
        'cnpj',
        'ie',
        'im',
        'email',
        'phone',
        'website',
        'logo',
        'postal_code',
        'address',
        'address_number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'country',
        'crt',
        'cnae',
        'cnae_secondary',
        'settings',
        'is_active',
        'is_matrix',
    ];

    protected $casts = [
        'cnae_secondary' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_matrix' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeMatrix($query)
    {
        return $query->where('is_matrix', true);
    }

    public function getFormattedCnpjAttribute(): string
    {
        $cnpj = preg_replace('/\D/', '', $this->cnpj);
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->address_number}, {$this->neighborhood}, {$this->city}/{$this->state}";
    }
}
