<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'name',
        'cnpj',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'salesperson_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the client.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the salesperson for the client.
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    /**
     * Get the shipments for the client.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'sender_client_id');
    }

    /**
     * Get the addresses for the client.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    /**
     * Get the proposals for the client.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * Get the invoices for the client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
