<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Client extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'cnpj',
        'email',
        'phone',
        'phone_e164',
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

    /**
     * Get the user associated with the client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all user assignments for this client (multi-tenant support).
     */
    public function userAssignments(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * Get the freight tables associated with this client.
     */
    public function freightTables(): BelongsToMany
    {
        return $this->belongsToMany(FreightTable::class, 'client_freight_table')
            ->withTimestamps();
    }

    /**
     * Normalize phone number to E.164 format.
     */
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

    protected static function booted(): void
    {
        static::saving(function (Client $client) {
            // Normalize phone to E.164 format only if column exists
            if (\Schema::hasColumn('clients', 'phone_e164')) {
                if ($client->phone) {
                    $client->phone_e164 = self::normalizePhone($client->phone);
                } else {
                    $client->phone_e164 = null;
                }
            }
        });
    }
}
